<?php

namespace Interactions;

use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

use function Common\messageWithContent;

class Example extends BaseInteraction
{
    protected static string $id = "Example";
    protected static bool $runOnce = false;

    public static function handler(Interaction $interaction, Discord $discord)
    {
        $interaction->respondWithMessage(messageWithContent("Example"));
    }
}
