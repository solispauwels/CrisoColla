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
     * @param \String $type The type of the content, this parameter is optional, but this parameter could be
     *                      usefull because the contents can have different twigs templates and sizes by their type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @see CrisoColla\ThemeBundle\ThemeService()
     */
    public function contentAction($id, $type = null)
    {
        //@todo get the types and if the $type is set use this type for twig and their size

        $manager = $this->getDoctrine()->getManager();

        $content = $manager->getRepository("CrisoCollaContentBundle:Content")->findOneBy(array('id' => $id));

        if ($content) {
            if ($type) {

                $types = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $type));

                $contentType = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('content' => $content, 'type' => $types)
                );

                if ($types and $contentType) {
                    $size = $contentType->getSize();
                }
            } else {
                //default values

                $type = "default";
                $size = "span12";
            }

            $menu = $this->menuAction($id, $size, $type)->getContent();

            return $this->render(
                $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
                    "CrisoCollaContentBundle:types:$type.html.twig"
                ),
                array('content' => $content, 'size' => $size, 'menu' => $menu, 'type' => $type)
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
    public function typeAction($type)
    {
        $content = $this->getContentByType($type);

        if ($content) {
            $creator = $this->creatorAction($type)->getContent();

            return $this->render(
                'CrisoCollaContentBundle::layout.html.twig',
                array('content' => $content, 'creator' => $creator)
            );

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
        $response = "false";

        if (isset($_POST['title']) and isset($_POST['text']) and isset($_POST['type'])) {
            $title = $_POST['title'];
            $text = $_POST['text'];
            $generated = $_POST['generated_content'];

            $manager = $this->getDoctrine()->getManager();

            $content = new Content();

            $content->setTitle($title);
            $content->setContent($text);
            $content->setGeneratedContent($generated);

            $manager->persist($content);

            $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(
                array('name' => $_POST['type'])
            );

            if ($type) {
                $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                    array('back' => null, 'type' => $type)
                );

                $contentType = new Content2Type($first);

                $contentType->setContent($content);
                $contentType->setType($type);

                $manager->persist($contentType);

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

            foreach ($contentTypes as $contentType) {
                $contentType->detach();

                $manager->remove($contentType);
            }

            $manager->remove($content);
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
    public function creatorAction($type, $id = null, $content = null)
    {
        if ($id and !$content) {
            $manager = $this->getDoctrine()->getManager();

            $content = $manager->getRepository("CrisoCollaContentBundle:Content")->find($id);
        }

        if ($content) {
            return $this->render(
                'CrisoCollaContentBundle::creator.html.twig',
                array('content' => $content, 'type' => $type)
            );
        } else {
            return $this->render(
                'CrisoCollaContentBundle::creator.html.twig',
                array('type' => $type)
            );
        }
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
        return $this->render(
            'CrisoCollaContentBundle::menu.html.twig',
            array('id' => $id, 'size' => $size, 'type' => $type)
        );
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
    public function getContentByType($type = "home")
    {
        $manager = $this->getDoctrine()->getManager();

        $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => $type));

        if ($type) {
            $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(
                array('back' => null, 'type' => $type)
            );

            if ($first) {
                $content = "";

                for ($i = 0; $i < $type->getMaxContentPage() and $first != null; $i++) {
                    $menu = $this->menuAction(
                        $first->getContent()->getId(),
                        $first->getSize(),
                        $type->getName()
                    )->getContent();

                    $content .= $this->render(
                        $this->container->get('criso_colla_theme.theme_service')->defaultTemplate(
                            "CrisoCollaContentBundle:types:".$type->getName().".html.twig"
                        ),
                        array(
                            'content' => $first->getContent(),
                            'size' => $first->getSize(),
                            'menu' => $menu,
                            'type' => $type->getName()
                        )
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
