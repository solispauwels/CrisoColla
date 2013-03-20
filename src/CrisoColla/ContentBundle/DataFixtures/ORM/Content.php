<?php

namespace CrisoColla\ContentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CrisoColla\ContentBundle\Entity\Content;

class Categories implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('Test1', 'Test2', 'Test2', 'Test3');

        foreach($fixtures as $i => $fixture)
        {
            $content[$i] = new Categorie();
            $content[$i]->setContent($fixture);

            $manager->persist($content[$i]);
        }

        $manager->flush();
    }
}
