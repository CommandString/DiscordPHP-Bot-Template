<?php

namespace Commands\Message;

use Core\Commands\DynamicCommand;
use Discord\Parts\Channel\Message;
use Core\Commands\MessageCommand;
use Exception;

#[MessageCommand]
class Ping extends DynamicCommand
{
    public function __construct()
    {

    }

    public function sendPing(Message $message)
    {
        throw new Exception('Exception Test');
    }
}
