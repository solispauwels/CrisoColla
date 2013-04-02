<?php

namespace CrisoColla\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CrisoColla\ContentBundle\Entity\Content;
use CrisoColla\ContentBundle\Entity\Type;
use CrisoColla\ContentBundle\Entity\Content2Type;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    /**
     *  Get content by id, if the content does not exists an error is given.
     *  This method require criso_colla_theme.theme_service.
     *
     * @param \String $id The id of the content.
     * @param \String $type The type of the content, this parameter is optional, but this parameter could be usefull because the contents can have different twigs templates and sizes by their type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see CrisoColla\ThemeBundle\ThemeService()
     */
    public function contentAction($id, $type = null)
    {
        //@todo get the types and if the $type is set use this type for twig and their size

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("CrisoCollaContentBundle:Content")->findOneBy(array('id' => $id));
        
        if($content)
        {
            if($type)
            {
                $types = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $type));
                $content2type = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(array('content' => $content, 'type' => $types));

                if($types and $content2type)
                {
                    $size = $content2type->getSize();
                }
            }
            else
            {
                //default values

                $type = "home"; 
                $size = "span12";
            }
        
            $menu = $this->menuAction($id, $size, $type)->getContent();

            return $this->render(
                $this->container->get('criso_colla_theme.theme_service')->defaultTemplate("CrisoCollaContentBundle:types:$type.html.twig"), 
                array('content' => $content, 'size' => $size, 'menu' => $menu));
        }
        else
        {
            return $this->render('CrisoCollaThemeBundle::error.html.twig', array('path' => "Content ".$id));
        }
    }

    /**
     * Render the layout of contents by type, if the type does not exists an error is given.
     * 
     * @param \String $type The type of contents.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * Create new content by POST method. This is used by ajax.
     * The response is the id of the new content in success, otherwise the response is the false word in a string. 
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $response = "false";

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

    /**
     * Update a content by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false. 
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * Render the HTML of the creator box.
     *
     * @param \String $type The type of the content to create.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorAction($type)
    {
        return $this->render('CrisoCollaContentBundle::creator.html.twig', array('type' => $type));
    }

    /**
     * Render the HTML of the menu in a content.
     *
     * @param \String $id The id of the content.
     * @param \String $size The size (span12) of the content.
     * @param \String $type The type of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction($id, $size, $type)
    {
        return $this->render('CrisoCollaContentBundle::menu.html.twig', array('id' => $id, 'size' => $size, 'type' => $type));
    }

    /**
     * Get Content by type, this method return an array with the content on success or null if the type does not exist.
     *
     * @param \String $type  Name of the type.
     *
     * @return \Array
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

                    $content.= $this->render(
                        $this->container->get('criso_colla_theme.theme_service')->defaultTemplate("CrisoCollaContentBundle:types:".$type->getName().".html.twig"), 
                        array('content' => $first->getContent(), 'size' => $first->getSize(), 'menu' => $menu))->getContent();
                    $first = $first->getNext();
                }

                return $content;
            }  
        }

        return null;
    }
}
