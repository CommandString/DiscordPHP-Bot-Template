<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\CommandPermissions;

#[Event(\Discord\WebSockets\Event::MESSAGE_CREATE)]
interface MessageCreate
{
    public function handle(Message $message): void;
}