<?php

namespace Tests;

use Core\Commands\Command;
use PHPUnit\Framework\TestCase;

class CommandAttributeTest extends TestCase
{
    public function testItRejectsBadSnowflakes(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Guild ID must be alphanumeric');

        new Command('not a snowflake');
    }

    public function testItAcceptsGoodSnowflakes(): void
    {
        $this->expectNotToPerformAssertions();

        new Command('1234567890');
    }
}
