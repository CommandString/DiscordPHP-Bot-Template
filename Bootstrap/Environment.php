<?php

use Core\Env;

$env = Env::createAuto();

if (!isset($env->TOKEN)) {
    throw new RuntimeException('No token supplied to environment!');
}
