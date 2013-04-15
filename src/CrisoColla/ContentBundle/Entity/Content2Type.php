<?php

namespace CrisoColla\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content2Type
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CrisoColla\ContentBundle\Entity\Content2TypeRepository")
 */
class Content2Type
{
    public function __construct($first)
    {
        $this->size = "span12"; //The size may be between 1 and 12 that correspont to span1 ant span12 of bootstrap

        if($first)
        {
            $first->setBack($this);
            $this->next = $first;
            $this->back = null;
        }
        else
        {
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
     * @ORM\ManyToOne(targetEntity="CrisoColla\ContentBundle\Entity\Type")
     * @ORM\JoinColumn(nullable=false)
    */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=30)
     */
    private $size;

    /**
    * @ORM\ManyToOne(targetEntity="CrisoColla\ContentBundle\Entity\Content2Type")
    */
    private $next;

    /**
    * @ORM\ManyToOne(targetEntity="CrisoColla\ContentBundle\Entity\Content2Type")
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
     * @return Content2Type
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
     * @return Content2Type
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
     * Set type
     *
     * @param \CrisoColla\ContentBundle\Entity\Type $type
     * @return Content2Type
     */
    public function setType(\CrisoColla\ContentBundle\Entity\Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \CrisoColla\ContentBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set next
     *
     * @param \CrisoColla\ContentBundle\Entity\Content2Type $next
     * @return Content2Type
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \CrisoColla\ContentBundle\Entity\Content2Type
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back
     *
     * @param \CrisoColla\ContentBundle\Entity\Content2Type $back
     * @return \CrisoColla\ContentBundle\Entity\Content2Type
     */
    public function setBack($back)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back
     *
     * @return \CrisoColla\ContentBundle\Entity\Content2Type
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * Detach a content from a type, this function can be used for reorder or delete contents.
     */
    public function detach()
    {
        if($this->getBack())
        {
            $this->getBack()->setNext($this->getNext());
        }

        if($this->getNext())
        {
            $this->getNext()->setBack($this->getBack());
        }
    }
}
