<?php

namespace Interactions;

use Classes\Utils;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

class Ping extends Template {
    protected static string $id = "Ping";

    public static function handler(Interaction $interaction, Discord $discord)
    {
        \Commands\Ping::handler($interaction);
    }
}