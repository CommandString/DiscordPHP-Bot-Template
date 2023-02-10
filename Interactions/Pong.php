<?php

namespace Interactions;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

class Pong extends BaseInteraction {
    protected static string $id = "Pong";

    public static function handler(Interaction $interaction, Discord $discord, int $timesPonged = null)
    {
        $timesPonged++;
        $interaction->respondWithMessage(MessageBuilder::new()->setContent("Ping {$timesPonged} :ping_pong:")->addComponent(\Commands\Ping::getActionRow($timesPonged, true)), true);
    }
}
