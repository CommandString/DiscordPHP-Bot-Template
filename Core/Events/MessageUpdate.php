<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;

/** @see https://discord-php.github.io/DiscordPHP/guide/events/messages.html#message-update */
#[Event(\Discord\WebSockets\Event::MESSAGE_UPDATE)]
interface MessageUpdate
{
    public function handle(Message $message, Discord $discord, ?Message $oldMessage): void;
}
