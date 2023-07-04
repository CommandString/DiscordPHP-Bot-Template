<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Helpers\Collection;

/** @see https://discord-php.github.io/DiscordPHP/guide/events/messages.html#message-delete-bulk */
#[Event(\Discord\WebSockets\Event::MESSAGE_DELETE_BULK)]
interface MessageDeleteBulk
{
    public function handle(Collection $messages, Discord $discord): void;
}
