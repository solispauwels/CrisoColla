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
        $end = new \DateTime('now');

        $interval = $start->diff($end);

        $formats = array("%y", "%m", "%W", "%d", "%G", "%i", "%s");
        $translation["singular"] = array("%y" => "year", "%m" => "month", "%W" => "week", "%d" => "day", "%G" => "hour", "%i" => "minute", "%s" => "second");
        $translation["plural"] = array("%y" => "years", "%m" => "months", "%W" => "weeks", "%d" => "days", "%G" => "hours", "%i" => "minutes", "%s" => "seconds");

        foreach($formats as $format)
        {
            if($interval->format($format)>0)
            {
                return $this->translator->transChoice(
                    "%count% ".$translation["singular"][$format]." ago|%count% ".$translation["plural"][$format]." ago",
                    $interval->format($format),
                    array('%count%' => $interval->format($format)),
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

