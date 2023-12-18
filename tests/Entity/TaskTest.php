<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testIsDone()
    {
        $task = new Task();
        $this->assertIsBool($task->isDone());
    }
}
