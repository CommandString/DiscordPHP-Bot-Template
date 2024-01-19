<?php

namespace Tests;

namespace Tests;

use Commands\Message\Ping;
use PHPUnit\Framework\TestCase;

class DynamicCommandTest extends TestCase
{
    public function testCheckExpired()
    {
        // Create a instance of a Command that has a Dynamic Type
        $ping = new Ping();
        // Set the time expired of the command
        $ping->setTimeLimit(time() + 5);
        //$ping->addTimeLimit(5);
        $loop = \React\EventLoop\Factory::create();

        $testCase = $this;

        $loop->addTimer(6, function () use ($ping, $testCase) {

            $testCase->assertEquals(true, $ping->isCommandExpired());
        });

        $loop->run();

    }
}
