<?php

namespace Events;

use Core\Events\Init;
use Discord\Discord;

class Ready implements Init
{
    public function handle(Discord $discord): void
    {
        echo "Bot is ready!\n";
    }
}
