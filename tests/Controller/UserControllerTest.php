<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserControllerTest extends WebTestCase
{

    private KernelBrowser|null $client = null;
    private UserRepository|null $userRepository = null;
    private TaskRepository|null $taskRepository = null;
    private User|null $user = null;
    private User|null $userToUpgrade = null;
    private User|null $userAdmin = null;
    private User|null $userAnonyme = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
        $this->user = $this->userRepository->findOneByEmail('testuser1@test.com');
        $this->userToUpgrade = $this->userRepository->findOneByEmail('testuser0@test.com');
        $this->userAdmin = $this->userRepository->findOneByEmail('testuser2@test.com');
        $this->userAnonyme = $this->userRepository->findOneBy(['username' => 'anonyme']);

    }

    /**
     * test the access control to the user list
     *
     * @return void
     */
    public function testUserListAccess()
    {
        // $client = static::createClient();
        // $userRepository = static::getContainer()->get(UserRepository::class);
        // ROLE_USER user.
        // $user = $userRepository->findOneByEmail('testuser1@test.com');
        $this->client->loginUser($this->user);
        $this->client->request('GET', 'users/list');
        $this->assertResponseStatusCodeSame(403);

    }


    public function testList()
    {
        // $client = static::createClient();
        // $userRepository = static::getContainer()->get(UserRepository::class);
        // $user = $userRepository->findOneByEmail('testuser2@test.com');
        $this->client->loginUser($this->userAdmin);
        $this->client->request('GET', 'users/list');
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
        // $client = static::createClient();
        // $userRepository = static::getContainer()->get(UserRepository::class);
        // // ROLE_USER user.
        // $user = $userRepository->findOneByEmail('testuser1@test.com');
        $this->client->loginUser($this->user);
        $this->client->request('GET', 'users/create');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-danger', "Oops ! Vous avez déjà un compte.");
    }

    /**
     * creating an user account
     *
     * @return void
     */
    public function testUserCreateForm()
    {
        // $client = static::createClient();
        // $userRepository = static::getContainer()->get(UserRepository::class);
        // ROLE_ADMIN user.
        // $user = $userRepository->findOneByEmail('testuser2@test.com');
        $this->client->loginUser($this->userAdmin);
        $crawler = $this->client->request('GET', '/users/create');
        $button = $crawler->selectButton('Ajouter');
        $form = $button->form();
        $this->client->submit(
            $form,
            [
                sprintf('%s[username]', $form->getName()) => "Test name",
                sprintf('%s[email]', $form->getName()) => "emailtest@test.fr",
                sprintf('%s[password][first]', $form->getName()) => "123456",
                sprintf('%s[password][second]', $form->getName()) => "123456",
            ]
        );
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! L'utilisateur a bien été ajouté.");

    }

    /**
     * A ROLE_ADMIN can upgrade a role
     *
     * @return void
     */
    public function testToggleRole()
    {
        // $client = static::createClient();
        // $userRepository = static::getContainer()->get(UserRepository::class);
        // // ROLE_ADMIN user.
        // $user = $userRepository->findOneByEmail('testuser2@test.com');
        // $userToUpgrade = $userRepository->findOneByEmail('testuser0@test.com');
        $urlId = $this->userToUpgrade->getId();
        $this->client->loginUser($this->userAdmin);
        $crawler = $this->client->request('GET', sprintf('/users/%s/toggle', $urlId));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Modifier les droits");
        $this->assertPageTitleContains('Modifier droits');
        $button = $crawler->selectButton('Modifier');
        $form = $button->form();
        $form[sprintf('%s[roles]', $form->getName())]->tick();
        $this->client->submit($form);
        $this->assertResponseRedirects('/users/list');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! Le rôle a bien était modifié");
        $this->assertTrue(in_array('ROLE_ADMIN', $this->userRepository->findOneByEmail('testuser0@test.com')->getRoles()));

    }


}