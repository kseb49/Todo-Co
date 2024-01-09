<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixturesTests extends Fixture implements FixtureGroupInterface
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    /**
     * Create 3 test users and 25 tasks
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $roles = [['ROLE_USER'], ['ROLE_ADMIN'], ['ROLE_SUPER_ADMIN'] ];
        for ($i=0; $i < 4; $i++) {
            $user = new User;
            $user->setEmail('testuser'.$i.'@test.com');
            if ($i < 2) {
                $user->setRoles($roles[0]);
            }
            if ($i >= 2 ) {
                $user->setRoles($roles[$i - 1]);
            }
            $user->setUsername($faker->userName());
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $manager->persist($user);
        }
        $manager->flush();

        $users = $manager->getRepository(User::class)->findAll();
        for ($i=0; $i < 25; $i++) {
            $task =new Task();
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
