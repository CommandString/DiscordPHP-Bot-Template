<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Parts\Guild\CommandPermissions;

#[Event(\Discord\WebSockets\Event::APPLICATION_COMMAND_PERMISSIONS_UPDATE)]
interface ApplicationCommandPermissionsUpdate
{
    public function handle(CommandPermissions $commandPermission, Discord $discord, ?CommandPermissions $oldCommandPermission): void;
}
