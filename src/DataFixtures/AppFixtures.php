<?php

namespace App\DataFixtures;

use App\Entity\Alcohol;
use App\Entity\Image;
use App\Entity\Producer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $producers = [];

        $producer1 = new Producer();
        $producer1->setName('Bacardi');
        $producer1->setCountry('Cuba');
        $manager->persist($producer1);
        $producers[] = $producer1;

        for ($i = 0; $i < 9; $i++) {
            $producer = new Producer();
            $producer->setName($faker->name);
            $producer->setCountry($faker->country);
            $manager->persist($producer);
            $producers[] = $producer;
        }

        $image1 = new Image();
        $image1->setName('Jameson');
        $image1->setUrl('sameUrl.com');
        $manager->persist($image1);

        $alcohol1 = new Alcohol();
        $alcohol1->setName('Jameson');
        $alcohol1->setType('whiskey');
        $alcohol1->setDescription('Tennessee whiskey');
        $alcohol1->setProducer($producer1);
        $alcohol1->setAbv(37.5);
        $alcohol1->setImage($image1);
        $manager->persist($alcohol1);

        for ($i = 0; $i < 49; $i++) {
            $alcohol = new Alcohol();
            $alcohol->setName($faker->unique()->name);
            $alcohol->setType($faker->randomElement(['vodka', 'beer', 'whiskey', 'wine', 'rum']));
            $alcohol->setDescription($faker->sentence());
            $alcohol->setProducer($producers[array_rand($producers)]);
            $alcohol->setAbv($faker->randomFloat(2, 4, 40));

            $image = new Image();
            $image->setName($alcohol->getName());
            $image->setUrl($faker->url);
            $alcohol->setImage($image);

            $manager->persist($image);
            $manager->persist($alcohol);
        }

        $manager->flush();
    }
}
