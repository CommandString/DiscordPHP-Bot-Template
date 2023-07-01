<?php

use Core\Commands\Command;
use Core\Commands\CommandQueue;
use Core\Commands\QueuedCommand;
use Core\Disabled;

use function Core\doesClassHaveAttribute;
use function Core\loopClasses;

$commandQueue = new CommandQueue();
loopClasses(BOT_ROOT . '/Commands', static function (string $className) use ($commandQueue) {
    $attribute = doesClassHaveAttribute($className, Command::class);
    $disabled = doesClassHaveAttribute($className, Disabled::class);

    if (!$attribute || $disabled !== false) {
        return;
    }

    $commandQueue->appendCommand(new QueuedCommand(
        $attribute->newInstance(),
        new $className()
    ));
});
$commandQueue->runQueue();
