<?php

namespace CrisoColla\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ThemeController extends Controller
{
	protected $theme;

	public function __construct()
	{
		/**
		 * @TODO Load from regions.
		 * @TODO Title, openGraph, Java Script, CSS and RSS
		 */
		$this->theme=array
			(
				//"header" => "",
				//"left" => "",
				//"right" => "",
				//"content" => "",
				"footer" => "CrisoColla 2013",	
			);

		$this->theme['theme']="Default";
	}
	
	public function renderPathAction($path="home")
	{
		//$this->initialize();

		$request = $this->get('router')->match("/notheme/$path");
		//print_r($request);

		if($this->getRequest()->attributes->get('_controller') != $request["_controller"])
		{
			$response = $this->forward($request["_controller"], $request);
			
			$this->theme["content"] = $response->getContent();
		}
		else
		{
			$this->theme["content"] = $this->render('CrisoCollaThemeBundle::error.html.twig', array('path' => $path))->getContent();
		}

		return $this->render('CrisoCollaThemeBundle:'.$this->theme['theme'].':layout.html.twig', $this->theme);
	}
}
