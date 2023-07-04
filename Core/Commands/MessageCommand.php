<?php

namespace Core\Commands;

use Attribute;
use Discord\Builders\MessageBuilder;

#[Attribute(Attribute::TARGET_CLASS)]
abstract class MessageCommand
{
    abstract public function getCommandDescription(): array;

    abstract public function help(): MessageBuilder;
}
