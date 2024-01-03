<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskControllerTest extends WebTestCase
{

    // private KernelBrowser|null $client = null;
    // private UserRepository|null $userRepository = null;
    // private User|null $user = null;

    // public function setUp(): void
    // {
    //     $this->client = static::createClient();
    //     $this->userRepository = static::getContainer()->get(UserRepository::class);
    //     $this->user = $this->userRepository->findOneByEmail('testuser0@test.com');
    //     $this->client->loginUser($this->user);
    // }


    public function testListRedirect()
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');

    }


    public function testList()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser0@test.com');
        $client->loginUser($user);
        // $this->client->loginUser($this->user);
        // $crawler = $this->client->request('GET', '/tasks');
        $crawler = $client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $link = $crawler->filter('a[href="/tasks/create"]')->text();
        $this->assertSame('Créer une tâche', $link);
        $this->assertSelectorTextContains('h4', "⬇️Vos Tâches⬇️");

    }


    public function testCreate()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser0@test.com');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/tasks/create');
        // $this->client->loginUser($this->user);
        // $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $button = $crawler->filter('button[type=submit]')->text();
        $this->assertSame('Ajouter', $button);
        $this->assertPageTitleContains('Créer une tache');
    }


    public function testCreateTaskForm()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser0@test.com');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/tasks/create');
        $button = $crawler->selectButton('Ajouter');
        $form = $button->form();
        $client->submit(
            $form,
            [
                sprintf('%s[title]', $form->getName()) => "Une tâche de test",
                sprintf('%s[content]', $form->getName()) => "Nouvelle tâche de test",
            ]
        );
        $client->followRedirect();
        $this->assertSelectorTextContains('div.alert.alert-success', "Superbe ! La tâche a été bien été ajoutée.");

    }

    public function testEdit()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser0@test.com');
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepository->findOneBy(['user' => $user]);
        $param = $task->getId();
        $client->loginUser($user);
        $crawler = $client->request('GET', '/tasks/'.$param.'/edit');
        $this->assertResponseIsSuccessful();
        $button = $crawler->filter('button[type=submit]')->text();
        $this->assertSelectorExists('form');
        $this->assertSame('Modifier', $button);
        $this->assertPageTitleContains('Modifier une tache');
        $this->assertFormValue('form', 'task_form[title]', $task->getTitle());
        $this->assertFormValue('form', 'task_form[content]', $task->getContent());
    }
    
}