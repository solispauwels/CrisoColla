<?php

namespace CrisoColla\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CrisoColla\ContentBundle\Entity\Content;
use CrisoColla\ContentBundle\Entity\Type;
use CrisoColla\ContentBundle\Entity\Content2Type;

class ContentController extends Controller
{

    public function indexAction($type)
    {
        $content = $this->getContentByType($type);

        if($content)
        {
            $creator = $this->creatorAction($type)->getContent();

            return $this->render('CrisoCollaContentBundle::index.html.twig', array('content' => $content, 'creator' => $creator));
        }
        else
        {
            return $this->render('CrisoCollaThemeBundle::error.html.twig', array('path' => $type));
        }
    }

    public function creatorAction($type)
    {
        return $this->render('CrisoCollaContentBundle::creator.html.twig', array('type' => $type));
    }

    public function menuAction($id, $size)
    {
        return $this->render('CrisoCollaContentBundle::menu.html.twig', array('id' => $id, 'size' => $size));
    }

    /**
     * Get Content by type
     *
     * @param String $type  Name of the type
     * @return Return an array with the content on success, and null if the type doesn't not exist
     */
    public function getContentByType($type = "home")
    {
        $manager = $this->getDoctrine()->getManager();

        $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $type));

        if($type)
        {
            $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(array('back' => null, 'type' => $type));

            if($first)
            {
                $content = "";

                for($i=0; $i < $type->getMaxContentPage() and $first != null; $i++)
                {
                    $menu = $this->menuAction($first->getId(), $first->getSize())->getContent();
                    
                    $content.= $this->render('CrisoCollaContentBundle:Default:default.html.twig', array('content' => $first->getContent(), 'size' => $first->getSize(), 'menu' => $menu))->getContent();
                    $first = $first->getNext();
                }

                return $content;
            }  
        }

        return null;
    }

    public function newContent()
    {
        /*$manager = $this->getDoctrine()->getManager();

        $content = new Content();

        $content->setTitle("Test");
        $content->setContent("hello world");

        $manager->persist($content);

        $types = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => 'home'));

        $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(array('back' => null, 'type' => $types));

        $content2type = new Content2Type($first);

        $content2type->setContent($content);
        $content2type->setType($types);

        $manager->persist($content2type);

        $manager->flush();*/
    }
}
