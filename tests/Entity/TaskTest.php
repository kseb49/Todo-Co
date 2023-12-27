<?php

namespace App\Tests\Entity;

use App\Entity\Task;
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


}
