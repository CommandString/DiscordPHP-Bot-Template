<?php

use Core\Env;

use function Core\deleteAllFilesInDirectory;

$env = Env::createAuto();

if (!isset($env->TOKEN)) {
    throw new RuntimeException('No token supplied to environment!');
}

deleteAllFilesInDirectory(BOT_ROOT . '/Core/HMR/Cached');
