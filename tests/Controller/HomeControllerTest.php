<?php

namespace App\Tests\Entity;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class HomeControllerTest extends WebTestCase
{
    public function testHomePage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $link = $crawler->filter('a[href="/login"]')->text();
        $this->assertResponseIsSuccessful();
        $this->assertSame('Se connecter', $link);
    }


    public function testHomePageRedirect()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('testuser0@test.com');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/');
        $link = $crawler->filter('a[href="/logout"]')->text();
        $this->assertResponseIsSuccessful();
        $this->assertSame('Se déconnecter', $link);
    }// public function testListException()
    // {
    //     $this->expectException(AccessDeniedException::class);
    //     $client = static::createClient();
    //     $client->request('GET', '/list');
    //     // $repo  = $this->createMock(TaskRepository::class);
    //     // $task = new TaskController();
    //     // $task->list($repo);
    // }
}