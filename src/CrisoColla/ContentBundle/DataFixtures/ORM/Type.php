<?php

namespace CrisoColla\ContentBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CrisoColla\ContentBundle\Entity\Type;

class Types extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('home', 'menu', 'users', 'html', 'todo');

        foreach ($fixtures as $i => $fixture) {

            $types[$i] = new Type();
            $types[$i]->setName($fixture);

            $manager->persist($types[$i]);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
