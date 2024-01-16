<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class TasksFixtures extends Fixture
{


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
            $task->toggle($faker->boolean());
            $manager->persist($task);
        }

        $manager->flush();

    }


}
