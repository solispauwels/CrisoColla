<?php

namespace CrisoColla\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CrisoColla\ContentBundle\Entity\Content;

class DefaultController extends Controller
{
    /**
     * Get Content by type
     */
    public function indexAction($type)
    {
        $content = new Content();

        $content->setTitle("Test");
        $content->setContent("hello world");

        $manager = $this->getDoctrine()->getManager();

        $content2 = $manager->getRepository("CrisoCollaContentBundle:Content")->find(6);

        $manager->persist($content);
        $manager->flush();

        return $this->render('CrisoCollaContentBundle:Default:index.html.twig', array('type' => $type));
    }
}
