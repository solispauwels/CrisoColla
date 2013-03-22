<?php

namespace CrisoColla\ThemeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CrisoColla\ThemeBundle\Entity\Setting;

class Settings implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $Setting = new Setting();
        $Setting->setCompanyName("Claroline");
        $Setting->setEmail("jorgeluissolis@gmail.com");
        $Setting->setBrief("CrisoColla CMS");
        $Setting->setTheme("claroline");
        $Setting->setLang("en");
        $Setting->setHome("home");

        $manager->persist($Setting);

        $manager->flush();
    }
}
