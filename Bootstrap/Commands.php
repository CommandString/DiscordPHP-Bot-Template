<?php

use CommandString\Utils\FileSystemUtils;
use Core\Commands\Command;
use Core\Commands\CommandQueue;
use Core\Commands\QueuedCommand;
use Core\Disabled;

$loopClasses = static function (string $directory, callable $callback): void {
    $convertPathToNamespace = static fn (string $path): string => str_replace([realpath(BOT_ROOT), '/'], ['', '\\'], $path);

    foreach (FileSystemUtils::getAllFilesWithExtensions($directory, ['php']) as $file) {
        $className = basename($file, '.php');
        $path = dirname($file);
        $namespace = $convertPathToNamespace($path);
        $className = $namespace.'\\'.$className;

        $callback($className);
    }
};

/**
 * @template T
 *
 * @param class-string $class
 * @param class-string<T> $attribute
 *
 * @throws ReflectionException
 *
 * @return T|false
 */
$doesClassHaveAttribute = static function (string $class, string $attribute): object|false {
    return (new ReflectionClass($class))->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF)[0] ?? false;
};

$commandQueue = new CommandQueue();
$loopClasses(BOT_ROOT.'/Commands', static function (string $className) use ($doesClassHaveAttribute, $commandQueue) {
    $attribute = $doesClassHaveAttribute($className, Command::class);
    $disabled = $doesClassHaveAttribute($className, Disabled::class);

    if (!$attribute || $disabled !== false) {
        return;
    }

    $commandQueue->appendCommand(new QueuedCommand(
        $attribute->newInstance(),
        new $className()
    ));
});
$commandQueue->runQueue();
