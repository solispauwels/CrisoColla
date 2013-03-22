<?php

namespace CrisoColla\ContentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CrisoColla\ContentBundle\Entity\Content;
use CrisoColla\ContentBundle\Entity\Type;
use CrisoColla\ContentBundle\Entity\Content2Type;

class Contents implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('Test1', 'Test2', 'Test2', 'Test3');

        $type = $manager->getRepository("CrisoCollaContentBundle:Type")->findOneBy(array('name' => 'home'));

        if($type)
        {
            foreach($fixtures as $i => $fixture)
            {
                $content[$i] = new Content();
                $content[$i]->setTitle($fixture);
                $content[$i]->setContent("hello world");

                $first = $manager->getRepository("CrisoCollaContentBundle:Content2Type")->findOneBy(array('back' => null, 'type' => $type));

                $content2type = new Content2Type($first);

                $content2type->setContent($content[$i]);
                $content2type->setType($type);

                $manager->persist($content2type);
                
                $manager->persist($content[$i]);

                $manager->flush();
                
            }
        }
    }
}
