<?php

namespace Events;

use Core\Events\Init;
use Core\Events\MessageCreate;
use Discord\Discord;
use Discord\Parts\Channel\Message as ChannelMessage;

use function Core\env;

class Message implements MessageCreate
{
    public function handle(ChannelMessage $message): void
    {

    }
}