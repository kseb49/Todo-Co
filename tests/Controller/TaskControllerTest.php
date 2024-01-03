<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskControllerTest extends WebTestCase
{

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

    // public function testEdit()
    // {
    //     $client = static::createClient();
    //     $userRepository = static::getContainer()->get(UserRepository::class);
    //     $user = $userRepository->findOneByEmail('testuser0@test.com');
    //     // $taskRepository = static::getContainer()->get(TaskRepository::class);
    //     // $task = $taskRepository->findBy(['user' => $user]);
    //     // $param = $task[0]->getId();
    //     $client->loginUser($user);
    //     // $crawler = $client->request('GET', '/tasks/{id}/edit', ['id' => $param]);
    //     $this->assertResponseIsSuccessful();
    //     $button = $crawler->filter('button[type=submit]')->text();
    //     $this->assertSelectorExists('form');
    //     $this->assertSame('Modifier', $button);
    //     $this->assertPageTitleContains('Modifier une tache');
    //     $this->assertFormValue('form', 'task_form[title]', $task[0]->getTitle());
    //     $this->assertFormValue('form', 'task_form[content]', $task[0]->getContent());
    // }
    
}