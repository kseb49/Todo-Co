<?php

namespace App\Tests\Entity;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TaskTest extends TestCase
{


    public function testIsDone()
    {
        $task = new Task();
        $this->assertIsBool($task->isDone());

    }


    #[DataProvider('boolProvider')]
    public function testToggle(bool $boolean)
    {
        $task = new Task();
        $task->toggle($boolean);
        $this->assertSame($boolean, $task->isDone());

    }


    public static function boolProvider(): array
    {
        return
        [
            [TRUE],
            [FALSE],
        ];

    }


    public function testSetCreatedAt()
    {
        $task = new Task();
        $task->setCreatedAt();
        $this->assertInstanceOf(DateTime::class, $task->getCreatedAt());

    }


    public function testSetUser()
    {
        $task = new Task();
        $user = $this->createStub(User::class);
        $task->setUser($user);
        $this->assertSame($user, $task->getUser());

    }


    public function testAddTask()
    {
        $task = new Task();
        $user = $this->createStub(User::class);
        $task->setUser($user);
        $task->setContent('new content');
        $task->setTitle('new title');
        $this->assertSame('new content', $task->getContent());
        $this->assertSame('new title', $task->getTitle());
        $this->assertInstanceOf(DateTime::class, $task->getCreatedAt());
        $this->assertInstanceOf(User::class, $user);

    }


    public function testReferer()
    {
        $task = new Task();
        $user = $this->createStub(User::class);
        $task->addReferer($user);
        $this->assertInstanceOf(Collection::class, $task->getReferer());
        $this->assertContains($user, $task->getReferer());
        $task->removeReferer($user);
        $this->assertNotContains($user, $task->getReferer());

    }


}
