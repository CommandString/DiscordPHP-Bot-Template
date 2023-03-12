<?php

namespace Commands\Examples\Subs;

use Commands\BaseCommand;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class Command extends BaseCommand
{

    protected static string|array $name = "command";

    public static function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(
            MessageBuilder::new()->setContent("👍")
        );
    }

    public static function getConfig(): CommandBuilder|array
    {
        return CommandBuilder::new()
            ->setName(new static());
    }
}