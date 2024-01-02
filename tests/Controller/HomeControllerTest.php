<?php

namespace App\Tests\Entity;

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