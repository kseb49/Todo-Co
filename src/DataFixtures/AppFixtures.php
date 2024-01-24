<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{


    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }


    public static function getGroups(): array
    {
        return ['dev'];

    }


    /**
     * Create 50 users
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $roles = [
                    ['ROLE_ADMIN'],
                    ['ROLE_USER'],
                 ];
        for ($i = 0; $i < 48; $i++) {
            $user = new User;
            $user->setEmail($faker->email());
            $user->setRoles($faker->randomElement($roles));
            $user->setUsername($faker->userName());
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $manager->persist($user);
        }

        // Anonymous user.
        $anonymousUser = new User;
        $anonymousUser->setEmail('fake@anonyme.com');
        $anonymousUser->setRoles(['ROLE_USER']);
        $anonymousUser->setUsername('anonyme');
        $anonymousUser->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $manager->persist($anonymousUser);

        // User with ROLE_SUPER_ADMIN.
        $superUser = new User;
        $superUser->setEmail($faker->email());
        $superUser->setRoles(['ROLE_SUPER_ADMIN']);
        $superUser->setUsername($faker->userName());
        $superUser->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $manager->persist($superUser);
        $manager->flush();

        // Create 100 tasks.
        $users = $manager->getRepository(User::class)->findAll();
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 100; $i++) {
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
