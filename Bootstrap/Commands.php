<?php

use Core\Commands\Command;
use Core\Commands\CommandQueue;
use Core\Commands\QueuedCommand;
use Core\Disabled;

use function Core\debug;
use function Core\discord;
use function Core\doesClassHaveAttribute;
use function Core\error;
use function Core\loopClasses;

$commandQueue = new CommandQueue();
$discord = discord();
loopClasses(BOT_ROOT . '/Commands', static function (string $className) use ($commandQueue) {
    debug('Loading Command: ' . $className);

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

$commandQueue->runQueue(registerCommands: Config::AUTO_REGISTER_COMMANDS)->otherwise(static fn (Throwable $e) => error($e->getMessage()));
