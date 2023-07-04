<?php

namespace Commands\Message;

use Core\Commands\BaseDynamicCommand;
use Core\Commands\MessageCommand;
use Discord\Parts\Channel\Message;

#[MessageCommand]
class Ping extends BaseDynamicCommand
{
    public function __construct()
    {
        $this->setTimeLimit(time());
        $this->addTimeLimit(1);
    }

    public function sayHello(Message $message)
    {
        $message->reply('pong!');
    }
}
