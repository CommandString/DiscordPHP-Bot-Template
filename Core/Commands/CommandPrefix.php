<?php

namespace Core\Commands;

interface CommandPrefix
{
    public function getPrefix(string $guildId): string;

    public function setPrefix(string $guildId, string $prefix): bool;
}
