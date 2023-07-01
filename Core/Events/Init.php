<?php

namespace Core\Events;

use Discord\Discord;

#[Event('init')]
interface Init
{
    public function handle(Discord $discord): void;
}
