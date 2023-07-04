<?php

use Commands\Message\Ping;
use Core\Commands\CommandCollection;
use Core\HMR\HotMessageCommand;
use Core\Disabled;
use Core\Commands\MessageCommand;
use Core\System;

use function Core\doesClassHaveAttribute;
use function Core\getFilePathFromClass;

$msgCommands = [
    'ping' => [
        'className' => Ping::class,
        'method' => 'sayHello',
        'instance' => null,
    ],
    // Other commands...
];

$commandCollection = new CommandCollection();

foreach ($msgCommands as $commandName => $value) {
    $className = $value['className'];
    $attribute = doesClassHaveAttribute($className, MessageCommand::class);
    $disabled = doesClassHaveAttribute($className, Disabled::class);

    if (!$attribute || $disabled !== false) {
        continue;
    }

    $filePath = getFilePathFromClass($className);
    $commandCollection->addHotCommand($commandName, $value, new HotMessageCommand($filePath));
}

System::get()->cmdCollection = $commandCollection;
