<?php

namespace App\DataFixtures;

use Faker\Factory;
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
        return ['test_user'];
    }

    /**
     * Create test users
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $roles = [['ROLE_ADMIN'], ['ROLE_USER'], ['ROLE_SUPER_ADMIN'] ];
        for ($i=0; $i < 3; $i++) {
            $user = new User;
            $user->setEmail('testuser'.$i.'@test.com');
            $user->setRoles($roles[$i]);
            $user->setUsername($faker->userName());
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $manager->persist($user);
        }

        $manager->flush();
    }


}
