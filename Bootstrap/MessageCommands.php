<?php

namespace Core\Commands;

use Commands\Message\Ping;
use Core\HMR\HotMessageCommand;
use Core\System;
use Core\Disabled;

use function Core\doesClassHaveAttribute;
use function Core\getFilePathFromClass;

//This is just an example, of how it is capable of handles
// this command can handle a command with 2 subcommand and 1 default subcommand
// $msgCommands = [
//     "mangadex" => [
//         "className" => MangaDex::class,
//         "subCommands" => [
//             "search" => "search",
//             "get" => "get"
//             // "subCommandName" => "methodName"
//         ],
//         "method" => "sendHelp",
//         "instance" => null,
//     ],
//     // Other commands...
// ];

$msgCommands = [
    'ping' => [
        'className' => Ping::class,
        'method' => 'sendPing',
        'instance' => null,
    ],
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
    $commandCollection->addHotCommand($commandName, $value, new HotMessageCommand($commandName, $filePath));
}

System::get()->cmdCollection = $commandCollection;
