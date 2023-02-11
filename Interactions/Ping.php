<?php

namespace Interactions;

use Commands\Ping as CommandsPing;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

class Ping extends BaseInteraction {
    protected static string $id = "Ping";

    public static function handler(Interaction $interaction, Discord $discord, int $timesPinged = null)
    {
        $timesPinged++;
        $interaction->respondWithMessage(MessageBuilder::new()->setContent("Pong {$timesPinged} :ping_pong:")->addComponent(CommandsPing::getActionRow($timesPinged, false)), true);
    }
}
