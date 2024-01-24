<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Application Tests class
 */
class UserControllerTest extends WebTestCase
{

    private KernelBrowser|null $client = null;

    private UserRepository|null $userRepository = null;

    /**
     * user with ROLE_USER
     *
     * @var User|null
     */
    private User|null $user = null;

    /**
     * user with ROLE_USER to be upgraded towards ROLE_ADMIN
     *
     * @var User|null
     */
    private User|null $userToToggle = null;

    /**
     * user with ROLE_ADMIN
     *
     * @var User|null
     */
    private User|null $userAdmin = null;

    /**
     * user with ROLE_SUPER_ADMIN
     *
     * @var User|null
     */
    private User|null $userSuperAdmin = null;


    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->user = $this->userRepository->findOneByEmail('testuser1@test.com');
        $this->userToToggle = $this->userRepository->findOneByEmail('testuser0@test.com');
        $this->userAdmin = $this->userRepository->findOneByEmail('testuser2@test.com');
        $this->userSuperAdmin = $this->userRepository->findOneByEmail('testuser3@test.com');

    }


    /**
     * test the access control to the user list
     *
     * @return void
     */
    public function testUserListAccess()
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', 'users/list');
        $this->assertResponseStatusCodeSame(403);

    }


    /**
     * Test the display of the users list page
     *
     * @return void
     */
    public function testList()
    {
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
        $this->client->loginUser($this->user);
        $this->client->request('GET', 'users/create');
        $this->assertResponseStatusCodeSame(403);

    }


    /**
     * creating an user account
     *
     * @return void
     */
    public function testUserCreateForm()
    {
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
     * editing an user account
     *
     * @return void
     */
    public function testUserEditForm()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', sprintf('/users/%s/edit', $this->user->getId()));
        $this->assertResponseIsSuccessful();
        $button = $crawler->filter('button[type=submit]');
        $this->assertSelectorExists('form');
        $this->assertSame('Modifier', $button->text());
        $this->assertPageTitleContains('Modification de compte');
        $this->assertFormValue('form', 'edit_user_form[username]', $this->user->getUsername());
        $this->assertFormValue('form', 'edit_user_form[email]', $this->user->getEmail());
        $form = $button->form();
        $this->client->submit(
            $form,
            [sprintf('%s[username]', $form->getName()) => "Edit name"]
        );
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! Modification réussie");
        $editedUser = $this->userRepository->find($this->user->getId());
        $this->assertSame("Edit name", $editedUser->getUsername());

    }


    /**
     * ROLE_ADMIN can Delete a user account
     *
     * @return void
     */
    public function testDeleteUser()
    {
        $userToDeleteId = $this->user->getId();
        $this->client->loginUser($this->userAdmin);
        $this->client->request('GET', '/users/list');
        $this->client->submitForm('delete-user'.$userToDeleteId);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! L'utilisateur a bien été supprimé");
        $this->assertNull($this->userRepository->find($userToDeleteId));

    }


    /**
     * A ROLE_ADMIN can upgrade a role
     *
     * @return void
     */
    public function testToggleRole()
    {
        $urlId = $this->userToToggle->getId();
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


    /**
     * A ROLE_SUPER_ADMIN can downgrade a role
     *
     * @return void
     */
    public function testDowngrade()
    {
        $urlId = $this->userToToggle->getId();
        $this->client->loginUser($this->userSuperAdmin);
        $crawler = $this->client->request('GET', sprintf('/users/%s/toggle', $urlId));
        $button = $crawler->selectButton('Modifier');
        $form = $button->form();
        $form[sprintf('%s[roles]', $form->getName())]->tick();
        $this->client->submit($form);
        $this->assertResponseRedirects('/users/list');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! Le rôle a bien était modifié");
        $this->assertTrue(in_array('ROLE_USER', $this->userRepository->findOneByEmail('testuser0@test.com')->getRoles()));

    }


    /**
     * Test the case the checkbox is not ticked
     *
     * @return void
     */
    public function testToggleRoleBlank()
    {
        $urlId = $this->userToToggle->getId();
        $this->client->loginUser($this->userAdmin);
        $crawler = $this->client->request('GET', sprintf('/users/%s/toggle', $urlId));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', "Modifier les droits");
        $this->assertPageTitleContains('Modifier droits');
        $button = $crawler->selectButton('Modifier');
        $form = $button->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/users/list');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-danger', "Vous n'avez pas modifié les droits de ce compte");
        $this->assertTrue(in_array('ROLE_USER', $this->userRepository->findOneByEmail('testuser0@test.com')->getRoles()));

    }


    /**
     * ROLE_ADMIN cannot Delete a ROLE_SUPER_ADMIN user account
     *
     * @return void
     */
    public function testDeleteAdminUser()
    {
        $this->client->loginUser($this->userAdmin);
        $this->client->request('GET', '/users/'.$this->userSuperAdmin->getId().'/delete');
        $this->assertResponseStatusCodeSame(403);

    }


    /**
     * Test the csrf protection
     *
     * @return void
     */
    public function testDeleteUserCsrf()
    {
        $userToDeleteId = $this->userToToggle->getId();
        $this->client->loginUser($this->userAdmin);
        $crawler = $this->client->request('GET', '/users/list');
        $button = $crawler->filter('#delete-user'.$userToDeleteId);
        $form = $button->form();
        $this->client->submit(
            $form,
            ['token' => "dummy token"]
        );
        $this->client->followRedirect();
        $this->assertSelectorTextContains(
            'div.alert.alert-danger',
            "Vous n'êtes pas autorisé à supprimer ce compte",
        );

    }


    /**
     * test login page
     *
     * @return void
     */
    public function testLogIn()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', "Se connecter");
        $this->assertPageTitleContains('Log in!');

    }


    /**
     * test redirection if already login
     *
     * @return void
     */
    public function testLogInRedirection()
    {
        $this->client->loginUser($this->userAdmin);
        $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/');

    }


    /**
     * test editing password
     *
     * @return void
     */
    public function testEditPassword()
    {
        $this->client->loginUser($this->userToToggle);
        $crawler = $this->client->request('GET', sprintf('/users/%s/editpass',$this->userToToggle->getId()));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('button', "Modifier");
        $this->assertPageTitleContains('Modifier votre mot de passe');
        $button = $crawler->selectButton('Modifier');
        $form = $button->form();
        $this->client->submit(
            $form,
            [
                sprintf('%s[password][first]', $form->getName()) => "new password",
                sprintf('%s[password][second]', $form->getName()) => "new password",
            ]);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! Votre mot de passe a était modifié",
        );

    }


}
