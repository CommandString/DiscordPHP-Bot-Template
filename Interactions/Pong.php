<?php

namespace Interactions;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

class Pong extends Template {
    protected static string $id = "Pong";

    public static function handler(Interaction $interaction, Discord $discord)
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('Ping :ping_pong:')->addComponent(\Commands\Ping::getActionRow()), true);
    }
}