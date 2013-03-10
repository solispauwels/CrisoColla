<?php

namespace CrisoColla\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
	/**
	 * Get Content by type
	 */
 	public function indexAction($type)
	{
		return $this->render('CrisoCollaContentBundle:Default:index.html.twig', array('type' => $type));
	}
}
