<?php

namespace App\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskControllerTest extends WebTestCase
{
    public function testListException()
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(403);
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