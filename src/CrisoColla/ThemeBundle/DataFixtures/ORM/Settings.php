<?php

namespace CrisoColla\ThemeBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CrisoColla\ThemeBundle\Entity\Setting;

class Settings extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $setting = new Setting();
        $setting->setCompanyName("Claroline");
        $setting->setEmail("jorgeluissolis@gmail.com");
        $setting->setBrief("CrisoColla CMS");
        $setting->setTheme("Claroline");
        $setting->setLang("en");
        $setting->setHome("home");

        $manager->persist($setting);

        $manager->flush();
    }

    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}
