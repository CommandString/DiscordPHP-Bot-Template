<?php

namespace Core\Manager;

use Core\Commands\CommandPrefix;
use Core\Database\DatabaseInterface;

use function Core\env;

/**
 * To manage Prefixes using the database
 */
class PrefixManager implements CommandPrefix
{
    private $cache = [];
    private $database;
    private $defaultPrefix;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
        $this->defaultPrefix = env()->DEFAULT_PREFIX;
        // Load all prefixes into the cache during instantiation
        $this->loadAllPrefixes();
    }

    private function loadAllPrefixes()
    {
        // Fetch all guilds' prefixes from the database
        $result = $this->database->query('SELECT id, prefix FROM guilds')->fetchAll();

        // Cache the prefixes
        foreach ($result as $row) {
            $this->cache[$row['id']] = $row['prefix'];
        }
    }

    public function getPrefix(string $guildId): string
    {
        // Check if prefix is cached, and
        if (isset($this->cache[$guildId])) {
            return $this->cache[$guildId];
        }

        // If a custom prefix is not set, use the default prefix,
        return $this->defaultPrefix;
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
