<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

class UserTest extends TestCase
{


    #[DataProvider('emailProvider')]
    public function testSetEmail(string $email)
    {
        $user = new User();
        $user->setEmail($email);
        $this->assertSame($email, $user->getEmail());

    }


    #[DataProvider('rolesProvider')]
    public function testSetRoles(array $role, array $roles)
    {
        $user = new User();
        $user->setRoles($role);
        $this->assertSame($roles, $user->getRoles());

    }


    #[DataProvider('userNameProvider')]
    public function testSetUserName(string $name)
    {
        $user = new User();
        $user->setUsername($name);
        $this->assertSame($name, $user->getUsername());

    }


    public function testGetTask()
    {
        $user = new User();
        $this->assertInstanceOf(Collection::class, $user->getTask());

    }


    public static function emailProvider(): array
    {
        return
        [
            ['fakeemail@email.com'],
            ['john@doe.com'],
        ];

    }


    public static function rolesProvider(): array
    {
        return
        [
            [['ROLE_ADMIN'], ['ROLE_ADMIN', 'ROLE_USER']],
            [[], ['ROLE_USER']],
        ];

    }


    public static function userNameProvider(): array
    {
        return
        [
            ['john'],
            ['jane'],
        ];

    }


    public function testMentionned()
    {
        $user = new User();
        $task = $this->createStub(Task::class);
        $user->addMentionned($task);
        $this->assertContains($task, $user->getMentionned());
        $this->assertInstanceOf(Collection::class, $user->getMentionned());
        $user->removeMentionned($task);
        $this->assertNotContains($task, $user->getMentionned());

    }


    public function testAddTask()
    {
        $task = new Task();
        $user = new User();
        $user->addTask($task);
        $this->assertContains($task, $user->getTask());

    }


    public function testRemoveTask()
    {
        $task = new Task();
        $user = new User();
        $user->removeTask($task);
        $this->assertNotContains($task, $user->getTask());

    }


}
