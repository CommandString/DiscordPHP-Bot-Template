<?php

namespace Core\Events;

use Discord\Discord;
use Discord\Parts\Guild\AutoModeration\Rule;

interface AutoModerationRuleCreate
{
    public function handle(Rule $rule, Discord $discord): void;
}
