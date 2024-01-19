<?php

use Commands\Message\Ping;
use Core\Commands\MessageCommandHandler;
use Core\Env;

$pingCommand = MessageCommandHandler::new()
    ->setCommandName('ping')
    ->setCommandClass(Ping::class)
    ->setDefaultMethod('sendPing');

$commandRepository = new Core\Commands\MessageCommandRepository();

$commandRepository->addMessageCommand($pingCommand);

Env::get()->cmdRepository = $commandRepository;
