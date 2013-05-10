<?php

namespace CrisoColla\ThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ThemeController extends Controller
{
    protected $theme;
    protected $settings;

    // @TODO Title, openGraph and RSS

    /**
     * Initialize this object, I can't use __construct because the services are not yet injected at that point.
     */
    public function initialize()
    {
        $manager = $this->getDoctrine()->getManager();

        if (empty($this->settings)) {

            $settings = $manager->getRepository("CrisoCollaThemeBundle:Setting")->findAll();

            $this->settings = $settings[0];
        }

        if (empty($this->theme)) {

            $regions = $manager->getRepository("CrisoCollaThemeBundle:Region")->findAll();

            foreach ($regions as $region) {

                $content = "";

                $first = $manager->getRepository("CrisoCollaThemeBundle:Content2Region")->findOneBy(
                    array('back' => null, 'region' => $region)
                );

                for ($i = 0; $first != null; $i++) {

                    $contentType = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                        array('content' => $first->getContent())
                    );

                    if ($contentType) {
                        $type = $contentType->getType()->getName();
                    } else {
                        $type = "default";
                    }

                    $content .= $this->render(
                        $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
                            "CrisoCollaContentBundle:types:".$type.".html.twig"
                        ),
                        array(
                            'content' => $first->getContent(),
                            'size' => $first->getSize(),
                            'menu' => "",
                            'type' => $type,
                            'region' => $region->getName()
                        )
                    )->getContent();

                    $first = $first->getNext();
                }

                if ($content != "") {
                    $this->theme[$region->getName()] = $content;
                }
            }

            $this->theme['theme'] = $this->settings->getTheme();
            $this->theme['title'] = $this->settings->getCompanyName();
        }
    }

    /**
     *  Render a theme with the content of another controller by their route,
     *  an error is given if the path does not exists.
     *
     *  This method require criso_colla_theme.theme_service.
     *
     * @param String $path The route of the contoller.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see CrisoColla\ThemeBundle\ThemeService()
     */
    public function renderPathAction($path = "home")
    {
        $this->initialize();

        $request = $this->get('router')->match("/notheme/$path");
        //print_r($request);

        if ($this->getRequest()->attributes->get('_controller') != $request["_controller"]) {
            $response = $this->forward($request["_controller"], $request);

            $this->theme["content"] = $response->getContent();
        } else {
            $this->theme["content"] = $this->render(
                'CrisoCollaThemeBundle::error.html.twig',
                array('path' => $path)
            )->getContent();
        }

        return $this->render(
            $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
                'CrisoCollaThemeBundle:'.$this->theme['theme'].':layout.html.twig'
            ),
            $this->theme
        );
    }
}
