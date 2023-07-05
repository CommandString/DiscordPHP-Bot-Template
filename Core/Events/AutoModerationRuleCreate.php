<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Parts\Guild\AutoModeration\Rule;

/** @see https://discord-php.github.io/DiscordPHP/guide/events/auto_moderations.html#auto-moderation-rule-create */
#[Event(\Discord\WebSockets\Event::AUTO_MODERATION_RULE_CREATE)]
interface AutoModerationRuleCreate
{
    public function handle(Rule $rule, Discord $discord): void;
}
