<?php

use function Core\discord;

const BOT_ROOT = __DIR__;
require_once __DIR__ . '/Bootstrap/Requires.php';

discord()->run(); // Run the bot
