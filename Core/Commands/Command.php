<?php

namespace Core\Commands;

use Attribute;
use LogicException;

#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
    public function __construct(
        public readonly string|array|null $name = null,
        public readonly ?string $guild = null,
    ) {
        if (isset($this->guild) && preg_match('/[^0-9]/', $this->guild)) {
            throw new LogicException('Guild ID must be alphanumeric');
        }
    }
}
