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
     * I can't use __construct because the services are not yet injected at that point
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
                "footer" => "CrisoColla 2013",	
            );

            $this->theme['theme']=$this->settings->getTheme();
        
        }
    }
    
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

        echo $this->defaultTemplate('CrisoCollaThemeBundle::erqsdfror.html.twig');

        return $this->render('CrisoCollaThemeBundle:'.$this->theme['theme'].':layout.html.twig', $this->theme);
    }

    public function defaultTemplate($path)
    {
        $dir = explode(":", $path);

        $file = explode(".", $dir[count($dir)-1]);

        if($file[count($file)]=="twig")
        {
            $base = __DIR__."/../Resources/views/";
        }
        else
        {
            $base = "/";
        }
        
        if($dir[1]=="")
        {
            $dir[0]=$dir[0].":";
            $tmp = array_slice($dir, 2);
        }
        else
        {
            $tmp = array_slice($dir, 1);

            if(!file_exists($base.$tmp[0]))
            {
                $tmp[0]="Default";
            }
        }

        if(file_exists($base.implode("/", $tmp)))
        {
            
            return $dir[0].":".implode(":", $tmp);
        }
        else
        {
            if($base=="/")
            {
                $file[0] = "defaultController";
            }
            else
            {
                $file[0] = "default";
            }

            $dir[count($dir)-1] = implode(".", $file);
            
            if(file_exists($base.implode("/", $tmp)))
            {
                return $dir[0].":".implode(":", $tmp);
            }
        }

        return $path;
    }
}
