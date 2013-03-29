<?php

namespace CrisoColla\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CrisoColla\ContentBundle\Entity\Content;
use CrisoColla\ContentBundle\Entity\Type;
use CrisoColla\ContentBundle\Entity\Content2Type;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    public function contentAction($id, $type="home")
    {
        //@todo get the types and if the $type is set use this type for twig and their size

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("CrisoCollaContentBundle:Content")->findOneBy(array('id' => $id));

        if($content){
            $size = "span12";

            $menu = $this->menuAction($id, $size, $type)->getContent();

            return $this->render('CrisoCollaContentBundle:Default:default.html.twig', 
                array('content' => $content, 'size' => $size, 'menu' => $menu));
        }
        else
        {
            return $this->render('CrisoCollaThemeBundle::error.html.twig', array('path' => "Content ".$id));
        }
    }

    public function typeAction($type)
    {
        $content = $this->getContentByType($type);

        if($content)
        {
            $creator = $this->creatorAction($type)->getContent();
            
            $sizes = $this->render('CrisoCollaContentBundle::sizes.html.twig')->getContent();

            return $this->render('CrisoCollaContentBundle::layout.html.twig', array('content' => $content, 'creator' => $creator, 'sizes' => $sizes));
        }
        else
        {
            return $this->render('CrisoCollaThemeBundle::error.html.twig', array('path' => $type));
        }
    }

    public function createAction()
    {
        $response = "false"; // in a string because this will be printed in a twig

        if(isset($_POST['title']) and isset($_POST['text']))
        {
            $title =  $_POST['title'];
            $text =  $_POST['text'];

            $manager = $this->getDoctrine()->getManager();

            $content = new Content();

            $content->setTitle($title);
            $content->setContent($text);

            $manager->persist($content);

            $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => 'home'));

            if($type)
            {
                $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(array('back' => null, 'type' => $type));

                $content2type = new Content2Type($first);

                $content2type->setContent($content);
                $content2type->setType($type);

                $manager->persist($content2type);

                $manager->flush();

                $response = $content->getId();
            }
        }

        return new Response($response);
    }

    public function updateAction($id)
    {
        $response = "false";

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("CrisoCollaContentBundle:Content")->findOneBy(array('id' => $id));

        if($content)
        {
            if(isset($_POST['title']))
            {
                $content->setTitle($_POST['title']);
            }

            if(isset($_POST['text']))
            {
                $content->setContent($_POST['text']);
            }

            if(isset($_POST['generated_content']))
            {
                $content->setGeneratedContent($_POST['generated_content']);
            }

            if(isset($_POST['size']) and isset($_POST['type']))
            {
                $type =  $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $_POST['type']));

                $content2type = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(array('content' => $content, 'type' => $type));

                if($content2type)
                {
                    $content2type->setSize($_POST['size']);
                    $manager->persist($content2type);
                }
            }

            if(isset($_POST['title']) or isset($_POST['text']) or isset($_POST['generated_content']) or (isset($_POST['size']) and isset($_POST['type'])))
            {
                $content->setModified();

                $manager->persist($content);
                $manager->flush();
    
                $response = "true";
            }
        }

        return new Response($response);
    }

    public function creatorAction($type)
    {
        return $this->render('CrisoCollaContentBundle::creator.html.twig', array('type' => $type));
    }

    public function menuAction($id, $size, $type)
    {
        return $this->render('CrisoCollaContentBundle::menu.html.twig', array('id' => $id, 'size' => $size, 'type' => $type));
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
                    $menu = $this->menuAction($first->getContent()->getId(), $first->getSize(), $type->getName())->getContent();

                    //@TODO use the type and not default -- and use a call of the action
                    $content.= $this->render('CrisoCollaContentBundle:Default:default.html.twig', array('content' => $first->getContent(), 'size' => $first->getSize(), 'menu' => $menu))->getContent();
                    $first = $first->getNext();
                }

                return $content;
            }  
        }

        return null;
    }
}
