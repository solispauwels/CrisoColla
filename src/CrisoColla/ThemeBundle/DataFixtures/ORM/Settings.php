<?php

namespace CrisoColla\ThemeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CrisoColla\ThemeBundle\Entity\Setting;

class Settings implements FixtureInterface
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
}
