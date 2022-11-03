<?php

namespace Discord\Bot\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

/**
 * @inheritDoc CommandTemplate
 */
class Ping extends Template {
    public function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('pong :ping_pong:'));
    }

    public function autocomplete(Interaction $interaction): void {
        
    }

    public function getName(): string
    {
        return "ping";
    }

    public function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName($this->getName())
            ->setDescription("Ping the bot")
            ->setDefaultMemberPermissions(0);
    }

    public function getGuild(): string
    {
        return "";
    }
}
