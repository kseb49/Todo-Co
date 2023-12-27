<?php

namespace App\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class HomeControllerTest extends WebTestCase
{
    public function testHomePage()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
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