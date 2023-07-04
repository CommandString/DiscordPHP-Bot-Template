<?php

use Core\Env;
use Discord\Discord;
use Discord\WebSockets\Intents;

use function Core\debug;
use function Core\discord as d;

Env::get()->discord = new Discord([
    'token' => Env::get()->TOKEN,
    'intents' => Intents::getAllIntents(),
]);

require_once BOT_ROOT . '/Bootstrap/Events.php';

d()->on('init', static function (Discord $discord) {
    debug('Bootstrapping Commands...');
    require_once BOT_ROOT . '/Bootstrap/Commands.php';
});
