<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Application Tests class
 */
class TaskControllerTest extends WebTestCase
{

    private KernelBrowser|null $client = null;

    private UserRepository|null $userRepository = null;

    private TaskRepository|null $taskRepository = null;

    /**
     * user with ROLE_USER
     *
     * @var User|null
     */
    private User|null $user = null;

    /**
     * user with ROLE_ADMIN
     *
     * @var User|null
     */
    private User|null $userAdmin = null;

    /**
     * the anonymous user
     *
     * @var User|null
     */
    private User|null $userAnonyme = null;


    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->taskRepository = static::getContainer()->get(TaskRepository::class);
        $this->user = $this->userRepository->findOneByEmail('testuser1@test.com');
        $this->userAdmin = $this->userRepository->findOneByEmail('testuser2@test.com');
        $this->userAnonyme = $this->userRepository->findOneBy(['username' => 'anonyme']);

    }


    /**
     * Delete a task (by its owner)
     *
     * @return void
     */
    public function testDeleteTask()
    {
        $task = $this->taskRepository->findOneBy(['user' => $this->user]);
        $taskId = $task->getId();
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/tasks');
        $button = $crawler->selectButton('Supprimer');
        $form = $button->form();
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! La tâche a bien été supprimée.");
        $this->assertEmpty($this->taskRepository->find($taskId));

    }


    /**
     * A ROLE_ADMIN user can delete an anonymous task
     *
     * @return void
     */
    public function testDeleteAnonyme()
    {
        $task = $this->taskRepository->findOneBy(['user' => $this->userAnonyme]);
        $taskId = $task->getId();
        $this->client->loginUser($this->userAdmin);
        $this->client->request('GET', '/tasks');
        $this->client->submitForm('delete'.$taskId);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! La tâche a bien été supprimée.");
        $this->assertEmpty($this->taskRepository->find($taskId));

    }


    /**
     * A ROLE_USER user cannot delete an anonymous task
     *
     * @return void
     */
    public function testDeleteAnonymeByUnauthorized()
    {
        $task = $this->taskRepository->findOneBy(['user' => $this->userAnonyme]);
        $taskId = $task->getId();
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/tasks/'.$taskId.'/delete');
        $this->assertResponseStatusCodeSame(403);

    }


    /**
     * Test the csrf protection to delete a task
     *
     * @return void
     */
    public function testDeleteCsrf()
    {
        $task = $this->taskRepository->findOneBy(['user' => $this->user]);
        $taskId = $task->getId();
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/tasks');
        // $this->client->submitForm('delete'.$taskId);
        $button = $crawler->filter('#delete'.$taskId);
        $form = $button->form();
        $this->client->submit(
            $form,
            ['token' => "dummy token"]
        );
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-danger', "Vous n'êtes pas autorisé à supprimer cette tâche");

    }


    /**
     * Test the toggle method
     *
     * @return void
     */
    public function testToggleState()
    {
        $this->client->loginUser($this->userAdmin);
        $this->client->request('GET', '/tasks');
        $task = $this->taskRepository->findOneBy(['user' => $this->userAdmin]);
        $taskState = $task->isDone();
        if ($taskState === true) {
            $text = sprintf("Superbe ! La tâche %s a bien été marquée en cours.", $task->getTitle());
        }

        if ($taskState === false) {
            $text = sprintf("Superbe ! La tâche %s a bien été marquée terminée.", $task->getTitle());
        }

        $this->client->submitForm('toggle'.$task->getId());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', $text);

    }


    /**
     * Test the csrf protection for togggle
     *
     * @return void
     */
    public function testToggleStateCsrf()
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/tasks');
        $task = $this->taskRepository->findOneBy(['user' => $this->user]);
        $this->client->submitForm('toggle'.$task->getId(), ['token' => "Dummy token"]);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-danger', "Oops ! Vous n'êtes pas autorisé à modifier cette tâche");

    }


    /**
     * test the access control to the users list
     *
     * @return void
     */
    public function testListRedirect()
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');

    }


    /**
     * Test the display of the list page
     *
     * @return void
     */
    public function testList()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $link = $crawler->filter('a[href="/tasks/create"]')->text();
        $this->assertSame('Créer une tâche', $link);
        $this->assertSelectorTextContains('h4', "⬇️Vos Tâches⬇️");
        $this->assertPageTitleContains('Liste des tâches');

    }


    /**
     * test the access to the create a task form
     *
     * @return void
     */
    public function testCreate()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $button = $crawler->filter('button[type=submit]')->text();
        $this->assertSame('Ajouter', $button);
        $this->assertPageTitleContains('Créer une tache');

    }


    /**
     * Test the success of creating a task
     *
     * @return void
     */
    public function testCreateTaskForm()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/tasks/create');
        $button = $crawler->selectButton('Ajouter');
        $form = $button->form();
        $form[sprintf('%s[referer]', $form->getName())] -> select($this->userAdmin->getId());
        $form[sprintf('%s[title]', $form->getName())] = "Une tâche de test";
        $form[sprintf('%s[content]', $form->getName())] = "Nouvelle tâche de test";
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! La tâche a été bien été ajoutée.");
        $taskUserId = $this->taskRepository->findOneBy(['title' => "Une tâche de test"])->getUser()->getId();
        $this->assertSame($this->user->getId(), $taskUserId);

    }


    /**
     * Test the display of the list page
     *
     * @return void
     */
    public function testListWithMentionned()
    {
        $this->client->loginUser($this->userAdmin);
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $link = $crawler->filter('a[href="/tasks/create"]')->text();
        $this->assertSame('Créer une tâche', $link);
        $this->assertNotEmpty($this->userAdmin->getMentionned());
        $title = '';
        foreach ($this->userAdmin->getMentionned() as $value) {
            $title .= $value->getTitle();
        }

        $this->assertAnySelectorTextContains('h4', $title);
        $this->assertPageTitleContains('Liste des tâches');

    }


    /**
     * Test the edit form
     *
     * @return void
     */
    public function testEdit()
    {
        $task = $this->taskRepository->findOneBy(['user' => $this->user]);
        $param = $task->getId();
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/tasks/'.$param.'/edit');
        $this->assertResponseIsSuccessful();
        $button = $crawler->filter('button[type=submit]');
        $this->assertSelectorExists('form');
        $this->assertSame('Modifier', $button->text());
        $this->assertPageTitleContains('Modifier une tache');
        $this->assertFormValue('form', 'task_form[title]', $task->getTitle());
        $this->assertFormValue('form', 'task_form[content]', $task->getContent());
        $form = $button->form();
        $this->client->submit(
            $form,
                [
                    sprintf('%s[title]', $form->getName()) => "Edited title",
                    sprintf('%s[content]', $form->getName()) => "edited content",
                ]
                );
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! La tâche a bien été modifiée.");
        $editedTask = $this->taskRepository->find($param);
        $this->assertSame("Edited title", $editedTask->getTitle());
        $this->assertSame("edited content", $editedTask->getContent());

    }


}
