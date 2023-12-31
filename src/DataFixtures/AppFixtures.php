<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

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
        $roles = [['ROLE_ADMIN'], ['ROLE_USER']];
        for ($i=0; $i < 49; $i++) {
            $user = new User;
            $user->setEmail($faker->email());
            $user->setRoles($faker->randomElement($roles));
            $user->setUsername($faker->userName());
            $user->setPassword($this->passwordHasher->hashPassword($user, '123456'));
            $manager->persist($user);
        }
        $anonymousUser = new User;
        $anonymousUser->setEmail('fake@anonyme.com');
        $anonymousUser->setRoles(['ROLE_USER']);
        $anonymousUser->setUsername('anonyme');
        $anonymousUser->setPassword($this->passwordHasher->hashPassword($user, '123456'));
        $manager->persist($anonymousUser);
        $manager->flush();
    }


}
