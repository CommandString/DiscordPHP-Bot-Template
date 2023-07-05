<?php

namespace Core\Events;

use Discord\Discord;

/** @see https://discord-php.github.io/DiscordPHP/guide/events/messages.html#message-delete */
#[Event(\Discord\WebSockets\Event::MESSAGE_DELETE)]
interface MessageDelete
{
    public function handle(object $message, Discord $discord): void;
}
