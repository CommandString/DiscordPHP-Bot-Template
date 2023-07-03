<?php

namespace Events;

use Core\Events\MessageCreate;
use Discord\Parts\Channel\Message as ChannelMessage;

class Message implements MessageCreate
{
    public function handle(ChannelMessage $message): void
    {

    }
}
