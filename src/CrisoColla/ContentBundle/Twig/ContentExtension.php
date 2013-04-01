<?php 

namespace CrisoColla\ContentBundle\Twig;

class ContentExtension extends \Twig_Extension
{
    protected $translator;

    public function __construct($translator)
    {
        $this->translator = $translator;
    }
        
    public function getFilters()
    {
        return array(
            'timeAgo' => new \Twig_Filter_Method($this, 'timeAgo'),
        );
    }

    function timeAgo($start)
    {
        $end = new \DateTime("now");
        
        $interval = $start->diff($end);

        $formats = array("%Y", "%m", "%W", "%d", "%H", "%i", "%s");
        $translation["singular"] = array("%Y" => "year", "%m" => "month", "%W" => "week", "%d" => "day", "%H" => "hour", "%i" => "minute", "%s" => "second");
        $translation["plural"] = array("%Y" => "years", "%m" => "months", "%W" => "weeks", "%d" => "days", "%H" => "hours", "%i" => "minutes", "%s" => "seconds");

        foreach($formats as $format)
        {
            if($format == "%W") //fix for week that does not exist in DataInterval obj
            {
                $i = round($interval->format("%d") / 8);
            }
            else
            {
                $i = ltrim($interval->format($format), "0");
            }

            if($i>0)
            {
                return $this->translator->transChoice(
                    "%count% ".$translation["singular"][$format]." ago|%count% ".$translation["plural"][$format]." ago",
                    $i,
                    array('%count%' => $i),
                    "content"
                );
            }
        }

        return $this->translator->transChoice(
            "%count% second ago|%count% seconds ago",
            1,
            array('%count%' => 1)
        );
    }

    public function getName()
    {
        return 'content_extension';
    }
}

