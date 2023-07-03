<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;

#[Event(\Discord\WebSockets\Event::MESSAGE_CREATE)]
interface MessageCreate
{
    public function handle(Message $message): void;
}
