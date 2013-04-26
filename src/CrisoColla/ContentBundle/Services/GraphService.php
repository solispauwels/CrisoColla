<?php

namespace CrisoColla\ContentBundle\Services;

use Symfony\Component\DomCrawler\Crawler;

class GraphService
{
    private $graph;
    private $crawler;

    public function get($url)
    {
        $this->graph["url"] = $url;

        $content = file_get_contents($url);

        $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

        $this->crawler = new Crawler($content);

        $this->openGraph();
        $this->twitter();

        if (!isset($this->graph["title"]) and !isset($this->graph["type"]) and !isset($this->graph["description"])) {
            $this->html();
        }

        //for example in case of slideshare:presentation
        $this->graph['type'] = str_replace(":", "-", $this->graph['type']);

        return $this->graph;
    }

    public function twitter()
    {
        $this->find(
            array('title', 'description', 'type', 'video', 'site_name', 'url', 'image'),
            "og",
            "property"
        );
    }

    public function openGraph()
    {
        $this->find(
            array('card', 'site', 'player', 'player:width', 'player:height', 'image'),
            "twitter",
            "name"
        );
    }

    public function find($values, $name, $attribute)
    {
        foreach ($values as $value) {
            try {
                $tmp = $this->crawler->filter("meta[$attribute='$name:$value']")->attr("content");

                if (!$tmp) {
                    $tmp = $this->crawler->filter("meta[$attribute='$name:$value']")->attr("value");
                }

                $this->graph[$value] = $tmp;
            } catch (\Exception $e) {
                $this->graph["error"] = 1;
            }
        }
    }

    public function html()
    {
        $this->graph["title"] = $this->crawler->filter("title")->text();
        $this->graph["type"] = "raw";

        $this->graph["description"] = "";

        $this->crawler->filter("body p")->each(
            function ($node, $i) {
                if (strlen($this->graph["description"]) < 100) {
                    $this->graph["description"] .= trim((new Crawler($node))->text())." ";
                }
            }
        );

        $this->crawler->filter("img")->each(
            function ($node, $i) {
                $this->graph["images"][$i] = (new Crawler($node))->attr("src");
            }
        );

    }
}
