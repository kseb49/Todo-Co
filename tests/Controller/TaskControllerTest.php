<?php

namespace App\Tests\Entity;

use App\Repository\UserRepository;
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
        $button = $crawler->filter('button[type=submit]')->text();
        $this->assertSelectorExists('form');
        $this->assertSame('Ajouter', $button);
        $this->assertPageTitleContains('Créer une tache');
    }
    // public function testListException()
    // {
    //     $this->expectException(AccessDeniedException::class);
    //     $client = static::createClient();
    //     $client->request('GET', '/list');
    //     // $repo  = $this->createMock(TaskRepository::class);
    //     // $task = new TaskController();
    //     // $task->list($repo);
    // }
}