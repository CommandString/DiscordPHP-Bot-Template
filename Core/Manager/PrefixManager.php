<?php

namespace Core\Manager;

use Core\Database\DatabaseInterface;

class PrefixManager
{
    private $cache = [];

    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getPrefix(string $guildId): string
    {
        // Check if prefix is cached
        if (isset($this->cache[$guildId])) {
            return $this->cache[$guildId];
        }

        // Get prefix from the database
        $prefix = $this->database->query("SELECT prefix FROM guilds WHERE id = '{$guildId}'")->fetchColumn();

        // Cache the prefix
        $this->cache[$guildId] = $prefix;

        return $prefix;
    }

    public function setPrefix(string $guildId, string $prefix): bool
    {
        // Update prefix in the cache
        $this->cache[$guildId] = $prefix;

        // Update prefix in the database
        $success = $this->database->query("UPDATE guilds SET prefix = '{$prefix}' WHERE id = '{$guildId}'");

        return (bool) $success;
    }
}
