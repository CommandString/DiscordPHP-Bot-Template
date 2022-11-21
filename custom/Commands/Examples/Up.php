<?php

namespace Discord\Bot\Commands\Examples;

use Discord\Bot\Commands\Template;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

// THIS SHOWS THAT SUBCOMMANDS CAN HAVE SEPARATE HANDLERS

/**
 * @inheritDoc CommandTemplate
 */
class Up extends Template {
    private Randomize $randomize;

    public function __construct() {
        $this->randomize = new Randomize();
    }

    public function handler(Interaction $interaction): void
    {
        // yes I know, technically I'm just calling methods from Randomize but ssshhh...
        $this->randomize->handler($interaction);
    }

    public function autocomplete(Interaction $interaction): void
    {
        $this->randomize->autocomplete($interaction);
    }

    public function getName(): array
    {
        return ["randomize", ["up"]];
    }

    public function getConfig(): CommandBuilder|array
    {
        return []; // no need to return config
    }

    public function getGuild(): string
    {
        return $this->randomize->guild; // probably want to do this so you only have to change it in one spot
    }
}
