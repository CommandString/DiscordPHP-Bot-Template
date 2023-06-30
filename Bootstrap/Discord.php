<?php

use Core\Env;
use Discord\Discord;
use Discord\WebSockets\Intents;

use function Core\discord as d;

Env::get()->discord = new Discord([
    'token' => Env::get()->TOKEN,
    'intents' => Intents::getAllIntents(),
]);

d()->on('init', static function (Discord $discord) {
    require_once BOT_ROOT.'/Bootstrap/Commands.php';
    //    require_once BOT_ROOT . '/Bootstrap/Events.php';
});
