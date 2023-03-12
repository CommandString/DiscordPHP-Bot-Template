<?php

namespace Commands\Examples;

use Commands\BaseCommand;
use Commands\Examples\Subs\Command;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Interaction;
use function Common\newOption;

class Sub extends BaseCommand
{

    protected static string|array $name = "sub";

    public static function handler(Interaction $interaction): void
    {
        $options = $interaction->data->options;

        if ($options != null) {
            if ($options->first()->name == "command")
                Command::handler($interaction);
        }
    }

    public static function getConfig(): CommandBuilder|array
    {
        return CommandBuilder::new()
            ->setName(new static())
            ->setDescription("Sub command")
            ->addOption(
                newOption(new Command(), "command option description", 1)
            );
    }
}