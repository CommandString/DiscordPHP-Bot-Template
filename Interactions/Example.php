<?php

namespace Interactions;

use Classes\Utils;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

class Example extends Template {
    protected static bool $runOnce = false;

    public static function handler(Interaction $interaction, Discord $discord)
    {
        $interaction->respondWithMessage(Utils::messageWithContent("Example"));
    }
}