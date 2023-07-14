<?php

use Core\Commands\Command;
use Core\Commands\CommandQueue;
use Core\Commands\QueuedCommand;
use Core\Disabled;
use Core\HMR\HotCommand;

use function Core\debug;
use function Core\discord;
use function Core\doesClassHaveAttribute;
use function Core\loopClasses;

$commandQueue = Config::AUTO_REGISTER_COMMANDS ? new CommandQueue() : null;
$discord = discord();
loopClasses(BOT_ROOT . '/Commands', static function (string $className) use ($commandQueue) {
    debug('Loading Command: ' . $className);

    $attribute = doesClassHaveAttribute($className, Command::class);
    $disabled = doesClassHaveAttribute($className, Disabled::class);

    if (!$attribute || $disabled !== false) {
        return;
    }

    $commandQueue?->appendCommand(new QueuedCommand(
        $attribute->newInstance(),
        new $className()
    ));
});

$commandQueue?->runQueue()->then(static function (array $commands) {
    foreach ($commands as $name => &$command) {
        $file = BOT_ROOT . '\\' . $command[0]->handler::class . '.php';

        new HotCommand(
            $name,
            $command[1],
            $file
        );
    }
})->otherwise(static fn (Throwable $e) => $discord->getLogger()->error($e->getMessage()));
