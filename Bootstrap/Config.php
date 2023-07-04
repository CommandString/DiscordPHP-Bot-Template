<?php

set_exception_handler([DegenerateZan\Utils\ExceptionHandler\ZanExceptionHandler::class, 'handle']);
define('DUMP_STACK_TRACE', false);
