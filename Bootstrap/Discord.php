<?php

use Core\Env;
use Discord\Discord;
use Discord\WebSockets\Intents;
use React\EventLoop\Loop;

use function Core\discord as d;

Env::get()->discord = new Discord([
    'token' => Env::get()->TOKEN,
    'intents' => Intents::getDefaultIntents()
                | Intents::MESSAGE_CONTENT,
    'loop' => Loop::get(),
]);

require_once BOT_ROOT . '/Bootstrap/Events.php';

d()->on('init', static function (Discord $discord) {
    require_once BOT_ROOT . '/Bootstrap/Commands.php';
});
