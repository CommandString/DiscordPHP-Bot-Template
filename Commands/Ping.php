<?php

namespace Commands;

use Commands\Template;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class Ping extends Template {
    public static function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('pong :ping_pong:'), true);
    }

    public static function getName(): string
    {
        return "ping";
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName(self::getName())
            ->setDescription("Ping the bot")
        ;
    }
}
