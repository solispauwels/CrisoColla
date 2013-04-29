<?php

namespace CrisoColla\ThemeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content2Region
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CrisoColla\ThemeBundle\Entity\Content2RegionRepository")
 */
class Content2Region
{
    public function __construct($first)
    {
        $this->size = "span12"; //The size may be between 1 and 12 that correspont to span1 ant span12 of bootstrap

        if ($first) {
            $first->setBack($this);
            $this->next = $first;
            $this->back = null;
        } else {
            $this->next = null;
            $this->back = null;
        }
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CrisoColla\ContentBundle\Entity\Content")
     * @ORM\JoinColumn(nullable=false)
    */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="CrisoColla\ThemeBundle\Entity\Region")
     * @ORM\JoinColumn(nullable=false)
    */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=255)
     */
    private $size;

    /**
    * @ORM\ManyToOne(targetEntity="CrisoColla\ThemeBundle\Entity\Content2Region")
    */
    private $next;

    /**
    * @ORM\ManyToOne(targetEntity="CrisoColla\ThemeBundle\Entity\Content2Region")
    */
    private $back;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set size
     *
     * @param string $size
     * @return Content2Region
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set content
     *
     * @param \CrisoColla\ContentBundle\Entity\Content $content
     * @return Content2Region
     */
    public function setContent(\CrisoColla\ContentBundle\Entity\Content $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return \CrisoColla\ContentBundle\Entity\Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set region
     *
     * @param \CrisoColla\ThemeBundle\Entity\Region $region
     * @return Content2Region
     */
    public function setRegion(\CrisoColla\ThemeBundle\Entity\Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \CrisoColla\ThemeBundle\Entity\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set next
     *
     * @param \CrisoColla\ThemeBundle\Entity\Content2Region $next
     * @return Content2Region
     */
    public function setNext(\CrisoColla\ThemeBundle\Entity\Content2Region $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \CrisoColla\ThemeBundle\Entity\Content2Region
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back
     *
     * @param \CrisoColla\ThemeBundle\Entity\Content2Region $back
     * @return Content2Region
     */
    public function setBack(\CrisoColla\ThemeBundle\Entity\Content2Region $back = null)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back
     *
     * @return \CrisoColla\ThemeBundle\Entity\Content2Region
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * Detach a content from a type, this function can be used for reorder or delete contents in regions.
     */
    public function detach()
    {
        if ($this->getBack()) {
            $this->getBack()->setNext($this->getNext());
        }

        if ($this->getNext()) {
            $this->getNext()->setBack($this->getBack());
        }
    }
}
