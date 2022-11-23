<?php

namespace Discord\Bot\Commands;

use Discord\Bot\Commands\Template;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Interaction;

class Example extends Template {
    public function handler(Interaction $interaction): void
    {
        
    }

    public function autocomplete(Interaction $interaction): void
    {
        
    }

    public function getName(): string
    {
        return "example";
    }

    public function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName($this->getName())
            ->setDescription("Example Command")
        ;
    }

    public function getGuild(): string
    {
        return "";
    }
}
