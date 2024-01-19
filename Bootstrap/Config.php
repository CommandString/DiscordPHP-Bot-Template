<?php

define('DUMP_STACK_TRACE', false);
set_exception_handler([DegenerateZan\Utils\ExceptionHandler\ZanExceptionHandler::class, 'handle']);

class Config
{
    public const AUTO_REGISTER_COMMANDS = true;
    public const AUTO_DELETE_COMMANDS = true;
}
