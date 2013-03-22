<?php

namespace CrisoColla\ContentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CrisoColla\ContentBundle\Entity\Type;

class Types implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('home', 'menu', 'users', 'html', 'todo');

        foreach($fixtures as $i => $fixture)
        {
            $types[$i] = new Type();
            $types[$i]->setName($fixture);

            $manager->persist($types[$i]);
        }

        $manager->flush();
    }
}
