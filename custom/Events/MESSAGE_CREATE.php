<?php

namespace Discord\Bot\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

/**
 * @inheritDoc Template
 */
class MESSAGE_CREATE extends Template {
    public function handler(Message $message = null, Discord $discord = null): void
    {
        if ($message->author->bot) {
            return;
        }

        $message->reply("Well I can't read what you said but I'm glad you said something :)");
    }
  
    public function getEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function runOnce(): bool
    {
        return false;
    }
}