<?php

namespace CrisoColla\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($path)
    {
       	
	
	$request = $this->get('router')->match("/notheme/$path");

	print_r($request);
        
	/**
	 * @TODO prevent infinit recursion
	 */
	$response = $this->forward($request["_controller"], $request );
	

	//$redirect = $this->redirect("notheme/$path");
	
	//$test=eval($request["_controller"]($request));

        //throw $this->createNotFoundException('The product does not exist');

	//$content = $this->render('CrisoCollaThemeBundle:Default:index.html.twig', array('content' => $path));

	return $this->render('CrisoCollaThemeBundle:Default:index.html.twig', array('content' => $response));
    }
}
