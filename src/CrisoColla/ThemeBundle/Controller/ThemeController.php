<?php

namespace CrisoColla\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ThemeController extends Controller
{
    protected $theme;
    protected $settings;

    // @TODO Load from regions.
    // @TODO Title, openGraph, Java Script, CSS and RSS

    /**
     * Initialize this object, I can't use __construct because the services are not yet injected at that point.
     */
    public function initialize()
    {
        if(empty($this->settings))
        {
            
            $manager = $this->getDoctrine()->getManager();

            $settings = $manager->getRepository("CrisoCollaThemeBundle:Setting")->findAll();

            $this->settings = $settings[0];
        }
        
        if(empty($this->theme))
        {
            $this->theme=array
            (
                //"header" => "",
                //"left" => "",
                //"right" => "",
                //"content" => "",
                "footer" => "Footer",	
            );

	    $this->theme['theme']=$this->settings->getTheme();
            $this->theme['title']=$this->settings->getCompanyName();
        }
    }
    
    /**
     *  Render a theme with the content of another controller by their route, an error is given if the path does not exists.
     *  This method require criso_colla_theme.theme_service.
     *
     * @param String $path The route of the contoller.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see CrisoColla\ThemeBundle\ThemeService()
     */
    public function renderPathAction($path="home")
    {
        $this->initialize();

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

        return $this->render(
            $this->container->get('criso_colla_theme.theme_service')->defaultTemplate('CrisoCollaThemeBundle:'.$this->theme['theme'].':layout.html.twig'), 
            $this->theme);
    }
}
