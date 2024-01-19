<?php

namespace Commands\Message;

use Core\Commands\DynamicCommand;
use Discord\Parts\Channel\Message;
use Core\Commands\MessageCommand;

#[MessageCommand]
class Ping extends DynamicCommand
{
    public function sendPing(Message $message)
    {
        $message->reply('Pong!');
    }
}
