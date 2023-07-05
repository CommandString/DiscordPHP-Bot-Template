<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;

/** @see https://discord-php.github.io/DiscordPHP/guide/events/messages.html#message-create */
#[Event(\Discord\WebSockets\Event::MESSAGE_CREATE)]
interface MessageCreate
{
    public function handle(Message $message, Discord $discord): void;
}
