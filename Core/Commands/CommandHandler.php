<?php

namespace Core\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Interaction;

interface CommandHandler
{
    public function handle(Interaction $interaction): void;

    public function autocomplete(Interaction $interaction): void;

    public function getConfig(): CommandBuilder|array;
}
