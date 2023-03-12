<?php

namespace Commands\Examples;

use Commands\BaseCommand;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Interaction;

class Example extends BaseCommand {
    protected static string $guild = "";
    protected static array|string $name = "Example";
    
    public static function handler(Interaction $interaction): void
    {
        
    }

    public static function autocomplete(Interaction $interaction): void
    {
        
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName(self::$name)
            ->setDescription("Example Command")
        ;
    }
}
