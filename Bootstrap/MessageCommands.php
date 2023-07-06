<?php

namespace Core\Commands;

use Commands\Message\Ping;
use Core\HMR\HotMessageCommand;
use Core\Commands\CommandCollection;
use Core\System;

use function Core\doesClassHaveAttribute;
use function Core\getFilePathFromClass;

// this is just an example, that it capeable to handles
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
    "ping" => [
        "className" => Ping::class,
        "method" => "sendPing",
        "instance" => null
    ]
    ];

$commandCollection = new CommandCollection();

foreach ($msgCommands as $commandName => $value) {
    $className = $value["className"];
    $attribute = doesClassHaveAttribute($className, MessageCommand::class);
    $disabled = doesClassHaveAttribute($className, Disabled::class);

    if (!$attribute || $disabled !== false) {
        continue;
    }

    $filePath = getFilePathFromClass($className);
    $commandCollection->addHotCommand($commandName, $value, new HotMessageCommand($commandName ,$filePath));
}

System::get()->cmdCollection = $commandCollection;
