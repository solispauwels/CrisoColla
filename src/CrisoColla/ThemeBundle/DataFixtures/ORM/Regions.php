<?php

namespace CrisoColla\ThemeBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CrisoColla\ContentBundle\Entity\Content;
use CrisoColla\ContentBundle\Entity\SubContent;
use CrisoColla\ThemeBundle\Entity\Region;
use CrisoColla\ThemeBundle\Entity\Content2Region;
use CrisoColla\ContentBundle\Entity\Type;
use CrisoColla\ContentBundle\Entity\Content2Type;

class Regions extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $this->setRegions($manager);
        $this->menuToRegion($manager, $this->menuAdmin($manager), 'left');
    }

    private function setRegions($manager)
    {
        $names = array('header', 'left', 'content', 'right', 'footer');

        foreach ($names as $name) {
            $region = new Region();

            $region->setName($name);

            $manager->persist($region);
        }

        $manager->flush();
    }

    private function menuAdmin($manager)
    {
        $links = array(
            "Content" => array("url" => "types", "icon" => "icon-th-large"),
            "Menus" => array("url" => "menu", "icon" => "icon-th-list")
        );

        $content = new Content($manager);
        $content->setTitle("Administration");

        $manager->persist($content);

        $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => 'menu'));

        $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
            array('back' => null, 'type' => $type)
        );

        $contentType = new Content2Type($first);
        $contentType->setContent($content);
        $contentType->setType($type);

        $manager->persist($contentType);

        $manager->flush();

        foreach ($links as $name => $link) {
            $linkContent = new Content();
            $linkContent->setTitle($name);
            $linkContent->setContent($link['url']);

            $manager->persist($linkContent);

            $first = $manager->getRepository("CrisoCollaContentBundle:SubContent")->findOneBy(
                array('back' => null, 'father' => $content)
            );

            $subContent = new SubContent($first);
            $subContent->setFather($content);
            $subContent->setChild($linkContent);

            $manager->persist($subContent);

            $manager->flush();
        }

        return $content;
    }

    public function menuToRegion($manager, $content, $regionName)
    {
        $region = $manager->getRepository("CrisoCollaThemeBundle:Region")->findOneBy(array('name' => $regionName));

        $first = $manager->getRepository("CrisoCollaThemeBundle:Content2Region")->findOneBy(
            array('back' => null, 'region' => $region)
        );

        $contentRegion = new Content2Region($first);
        $contentRegion->setContent($content);
        $contentRegion->setRegion($region);

        $manager->persist($contentRegion);

        $manager->flush();
    }

    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}
