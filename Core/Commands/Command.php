<?php

namespace Core\Commands;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
    public function __construct(
        public readonly string|array $name,
        public readonly ?string $guild = null,
    ) {
    }
}
