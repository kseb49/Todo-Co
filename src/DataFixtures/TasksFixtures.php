<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class TasksFixtures extends Fixture implements FixtureGroupInterface
{


    public static function getGroups(): array
    {
        return ['exc'];
    }


    /**
     * Create 100 tasks
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $faker = Factory::create('fr_FR');
        for ($i =0; $i < 100; $i++) {
            $task = new Task();
            $task->setTitle('Tache n°'.$i);
            $task->setContent($faker->text(35));
            $task->setCreatedAt($faker->dateTime());
            $task->setUser($faker->randomElement($users));
            $rand = rand(0, 5);
            for ($i=0; $i < $rand; $i++) { 
                $task->addReferer($faker->randomElement($users));
            }
            $task->toggle($faker->boolean());
            $manager->persist($task);
        }

        $manager->flush();

    }


}
