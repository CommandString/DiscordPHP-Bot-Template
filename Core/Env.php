<?php

namespace Core;

use Core\Database\DatabaseInterface;
use Core\Commands\MessageCommandRepository;
use Discord\Discord;
use Tnapf\Env\Env as BaseEnv;
use Core\Commands\CommandPrefix;

/**
 * @property-read string $TOKEN
 * @property Discord $discord
 * @property DatabaseInterface $db
 * @property MessageCommandRepository $messageCommandRepository
 * @property CommandPrefix $prefixManager
 */
class Env extends BaseEnv
{
}
