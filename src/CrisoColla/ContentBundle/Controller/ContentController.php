<?php

namespace CrisoColla\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CrisoColla\ContentBundle\Entity\Content;
use CrisoColla\ContentBundle\Entity\SubContent;
use CrisoColla\ContentBundle\Entity\Type;
use CrisoColla\ContentBundle\Entity\Content2Type;
use CrisoColla\ThemeBundle\Entity\Content2Region;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    /**
     *  Get content by id, if the content does not exists an error is given.
     *  This method require criso_colla_theme.theme_service.
     *
     * @param \String $id The id of the content.
     * @param \String $type The type of the content, this parameter is optional, but this parameter could be
     *                      usefull because the contents can have different twigs templates and sizes by their type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see CrisoColla\ThemeBundle\ThemeService()
     */
    public function contentAction($id, $type = null, $father = null)
    {
        $variables = array();
        $variables["type"] = $type;

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("CrisoCollaContentBundle:Content")->find($id);
        $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $type));

        if ($content) {
            if (!$type) {

                //default values

                $variables["type"] = "default";
                $variables["size"] = "span12";

            } else if ($father) {

                $variables["father"] = $father;

                $father = $manager->getRepository("CrisoCollaContentBundle:Content")->find($father);

                $subContent = $manager->getRepository("CrisoCollaContentBundle:SubContent")->findOneBy(
                    array('child' => $content, 'father' => $father)
                );

                $variables["size"] = $subContent->getSize();

            } else {

                $contentType = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('content' => $content, 'type' => $type)
                );

                $variables["size"] = $contentType->getSize();
            }

            $variables["menu"] = $this->menuAction($id, $variables["size"], $variables["type"], $father)->getContent();
            $variables["content"] = $content;

            return $this->render(
                $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
                    "CrisoCollaContentBundle:types:".$variables["type"].".html.twig"
                ),
                $variables
            );
        } else {
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
    public function typeAction($type, $father = null)
    {
        $content = $this->getContentByType($type, $father);

        $variables = array();

        if ($content) {

            $variables["content"] = $content;
            $variables["creator"] = $this->creatorAction($type, null, null, $father)->getContent();

            if ($father) {
                $variables["father"] = $father;
            }

            return $this->render("CrisoCollaContentBundle::layout.html.twig", $variables);

        } else {

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
        $request = $this->get('request');

        $response = "false";

        if (isset($_POST['title']) and isset($_POST['text']) and isset($_POST['type'])) {

            $manager = $this->getDoctrine()->getManager();

            $content = new Content();

            $content->setTitle($request->get('title'));
            $content->setContent($request->get('text'));
            $content->setGeneratedContent($request->get('generated'));

            $manager->persist($content);

            if ($request->get('father')) {
                $father = $manager->getRepository("CrisoCollaContentBundle:Content")->find($request->get('father'));

                $first = $manager->getRepository("CrisoCollaContentBundle:SubContent")->findOneBy(
                    array('back' => null, 'father' => $father)
                );

                $subContent = new SubContent($first);
                $subContent->setFather($father);
                $subContent->SetChild($content);

                $manager->persist($subContent);

            } else {

                $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(
                    array('name' => $_POST['type'])
                );

                $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('back' => null, 'type' => $type)
                );

                $contentType = new Content2Type($first);

                $contentType->setContent($content);
                $contentType->setType($type);

                $manager->persist($contentType);
            }

            $manager->flush();

            $response = $content->getId();
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

        if ($content) {

            $request = $this->get('request');

            $content->setTitle($request->get('title'));
            $content->setContent($request->get('text'));
            $content->setGeneratedContent($request->get('generated_content'));

            if ($request->get('size') and $request->get('type')) {
                $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(
                    array('name' => $request->get('type'))
                );

                $contentType = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('content' => $content, 'type' => $type)
                );

                $contentType->setSize($request->get('size'));
                $manager->persist($contentType);
            }

            if (
                $request->get('title') or
                $request->get('text') or
                $request->get('generated_content') or
                ($request->get('size') and $request->get('type'))
            ) {
                $content->setModified();

                $manager->persist($content);
                $manager->flush();

                $response = "true";
            }

        }

        return new Response($response);
    }

    /**
     * Reorder contents in types. This method is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @param \String $type The type of the content.
     * @param \String $a The id of the content 1.
     * @param \String $b The id of the content 2.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reorderAction($type, $a, $b)
    {
        $response = "false";

        $manager = $this->getDoctrine()->getManager();

        $a = $manager->getRepository("CrisoCollaContentBundle:Content")->find($a);
        $b = $manager->getRepository("CrisoCollaContentBundle:Content")->find($b);

        $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $type));

        if ($a and $type) {
            $a = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                array('type' => $type, 'content' => $a)
            );

            $a->detach();

            if ($b) {
                $b = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('type' => $type, 'content' => $b )
                );

                $a->setBack($b->getBack());
                $a->setNext($b);

                if ($b->getBack()) {
                    $b->getBack()->setNext($a);
                }

                $b->setBack($a);
            } else {
                $b = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('type' => $type, 'next' => null)
                );

                $a->setNext($b->getNext());
                $a->setBack($b);

                $b->setNext($a);
            }

            $manager->persist($a);
            $manager->persist($b);

            $manager->flush();

            $response = "true";
        }

        return new Response($response);
    }

    /**
     * Delete a content by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($id)
    {
        $response = "false";

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("CrisoCollaContentBundle:Content")->find($id);

        if ($content) {
            $contentTypes = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findBy(
                array('content' => $content)
            );

            $childs = $manager->getRepository("CrisoCollaContentBundle:SubContent")->findBy(
                array('father' => $content)
            );

            $fathers = $manager->getRepository("CrisoCollaContentBundle:SubContent")->findBy(
                array('child' => $content)
            );

            $regions = $manager->getRepository("CrisoCollaThemeBundle:Content2Region")->findBy(
                array('content' => $content)
            );

            foreach ($contentTypes as $contentType) {
                $contentType->detach();

                $manager->remove($contentType);
            }

            foreach ($childs as $child) {
                $child->detach();
                $manager->remove($child);

                $this->deleteAction($child->getChild()->getId());
            }

            foreach ($fathers as $father) {
                $father->detach();
                $manager->remove($father);
            }

            foreach ($regions as $region) {
                $region->detach();
                $manager->remove($region);
            }

            $manager->remove($content);
            $manager->flush();

            $response = "true";
        }

        return new Response($response);
    }

    public function contentToRegionAction($region, $id)
    {
        $response = "false";

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("CrisoCollaContentBundle:Content")->find($id);
        $region = $manager->getRepository("CrisoCollaThemeBundle:Region")->findOneBy(
            array("name" => $region)
        );

        if ($content and $region) {
            $first = $manager->getRepository("CrisoCollaThemeBundle:Content2Region")->findOneBy(
                array("back" => null, "region" => $region)
            );

            $contentRegion = new Content2Region($first);
            $contentRegion->setRegion($region);
            $contentRegion->setContent($content);

            $manager->persist($contentRegion);
            $manager->flush();

            $response = "true";
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
    public function creatorAction($type, $id = null, $content = null, $father = null)
    {
        $path = $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
            "CrisoCollaContentBundle:types:".$type.".creator.twig"
        );

        $variables = array('type' => $type);

        if ($id and !$content) {
            $manager = $this->getDoctrine()->getManager();

            $variables["content"] = $manager->getRepository("CrisoCollaContentBundle:Content")->find($id);
        }

        if ($father) {
            $variables['father'] = $father;
        }

        return $this->render($path, $variables);
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
    public function menuAction($id, $size, $type, $father = null)
    {
        $variables = array('id' => $id, 'size' => $size, 'type' => $type);

        if ($father) {
            $variables["father"] = $father->getId();
        }

        return $this->render('CrisoCollaContentBundle::menu.html.twig', $variables);
    }

    /**
     * Render the HTML of the menu of sizes of the contents.
     *
     * @param \String $id The id of the content.
     * @param \String $size The size (span12) of the content.
     * @param \String $type The type of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sizeAction($id, $size, $type)
    {
        return $this->render(
            'CrisoCollaContentBundle::sizes.html.twig',
            array('id' => $id, 'size' => $size, 'type' => $type)
        );
    }

    /**
     * Render the HTML of the regions.
     *
     * @param \String $id The id of the content.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regionAction($id)
    {
        return $this->render('CrisoCollaContentBundle::regions.html.twig', array('id' => $id));
    }

    /**
     * Render the HTML of a content generated by an external url with Open Grap meta tags
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function graphAction()
    {
        $response = "false";

        if (isset($_POST["generated_content_url"])) {
            $url = $_POST["generated_content_url"];

            $graph = $this->container->get('criso_colla_content.graph_service')->get($url);

            if (isset($graph['type'])) {
                $response = $this->render(
                    $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
                        "CrisoCollaContentBundle:generated:".$graph['type'].".html.twig"
                    ),
                    array('content' => $graph)
                )->getContent();
            }
        }

        return new Response($response);
    }

    /**
     * Get Content by type, this method return an array with the content on success or null if the type does not exist.
     *
     * @param \String $type  Name of the type.
     *
     * @return \Array
     */
    public function getContentByType($type = "home", $father = null)
    {
        $manager = $this->getDoctrine()->getManager();

        $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $type));

        if ($type) {
            if ($father) {

                $father = $manager->getRepository("CrisoCollaContentBundle:Content")->find($father);

                $first = $manager->getRepository("CrisoCollaContentBundle:SubContent")->findOneBy(
                    array('back' => null, 'father' => $father)
                );

            } else {
                $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('back' => null, 'type' => $type)
                );
            }

            if ($first) {
                $content = "";

                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $variables = array();

                    $variables["content"] = $first->getContent();
                    $variables["size"] = $first->getSize();
                    $variables["type"] = $type->getName();
                    $variables["menu"] = $this->menuAction(
                        $first->getContent()->getId(),
                        $first->getSize(),
                        $type->getName(),
                        $father
                    )->getContent();

                    if ($father) {
                        $variables["father"] = $father->getId();
                    }

                    $content .= $this->render(
                        $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
                            "CrisoCollaContentBundle:types:".$type->getName().".html.twig"
                        ),
                        $variables
                    )->getContent();

                    $first = $first->getNext();
                }

                return $content;
            }

            return " "; // Not yet content
        }

        return null;
    }
}
