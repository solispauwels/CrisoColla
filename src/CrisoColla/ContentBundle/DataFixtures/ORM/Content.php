<?php

namespace CrisoColla\ContentBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CrisoColla\ContentBundle\Entity\Content;
use CrisoColla\ContentBundle\Entity\Type;
use CrisoColla\ContentBundle\Entity\Content2Type;

class Contents extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $fixtures = array('Test1', 'Test2', 'Test3', 'Test4');

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

    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
    
}
