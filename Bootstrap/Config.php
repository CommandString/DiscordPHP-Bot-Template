<?php

use DegenerateZan\Utils\ExceptionHandler\ZanErrorHandler;
use DegenerateZan\Utils\ExceptionHandler\ZanExceptionHandler;

set_exception_handler([ZanExceptionHandler::class, 'handle']);
set_error_handler([ZanErrorHandler::class, 'handle']);
