<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserControllerTest extends WebTestCase
{

    /**
     * test the access control to the user list
     *
     * @return void
     */
    public function testUserListAccess()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        // ROLE_USER user.
        $user = $userRepository->findOneByEmail('testuser0@test.com');
        $client->loginUser($user);
        $client->request('GET', 'users/list');
        $this->assertResponseStatusCodeSame(403);

    }


    public function testUserList()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser1@test.com');
        $client->loginUser($user);
        $client->request('GET', 'users/list');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Liste des utilisateurs");
        $this->assertPageTitleContains('Liste des utilisateurs');

    }

    /**
     * test the access control to create an account
     *
     * @return void
     */
    public function testUserCreate()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser0@test.com');
        $client->loginUser($user);
        $client->request('GET', 'users/create');
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-danger', "Oops ! Vous avez déjà un compte.");
    }


    public function testUserCreateTaskForm()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser1@test.com');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/users/create');
        $button = $crawler->selectButton('Ajouter');
        $form = $button->form();
        $client->submit(
            $form,
            [
                sprintf('%s[username]', $form->getName()) => "Test name",
                sprintf('%s[email]', $form->getName()) => "emailtest@test.fr",
                sprintf('%s[password][first]', $form->getName()) => "123456",
                sprintf('%s[password][second]', $form->getName()) => "123456",
            ]
        );
        $client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! L'utilisateur a bien été ajouté.");

    }

}