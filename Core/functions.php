<?php

namespace Core;

use CommandString\Utils\FileSystemUtils;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Choice;
use Discord\Parts\Interactions\Command\Option as CommandOption;
use Discord\Parts\Interactions\Request\Option;
use Discord\Parts\Interactions\Interaction;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * Returns the Env instance
 *
 * @throws LogicException if the Env instance is not set
 */
function env(): Env
{
    $env = Env::get();

    if (!isset($env)) {
        throw new LogicException('Env is not set');
    }

    return $env;
}

/**
 * Returns the Discord instance from the Environment
 *
 * @throws LogicException if the Discord instance is not set
 */
function discord(): ?Discord
{
    return env()->discord ?? null;
}

function getFilePathFromClass($className)
{
    $reflection = new ReflectionClass($className);

    return $reflection->getFileName();
}

/**
 * Create a new Option used for building slash commands
 */
function newSlashCommandOption(string $name, string $description, int $type, bool $required = false): CommandOption
{
    return newDiscordPart(CommandOption::class)
        ->setName($name)
        ->setDescription($description)
        ->setType($type)
        ->setRequired($required);
}

/**
 * Create a new Choice used for building slash commands
 */
function newSlashCommandChoice(string $name, float|int|string $value): Choice
{
    return newDiscordPart(Choice::class)
        ->setName($name)
        ->setValue($value);
}

/**
 * Create a new MessageBuilder object with the content define for creating simple MessageBuilders quickly
 *
 * ```php
 * $message = messageWithContent("Hello World");
 * ```
 */
function messageWithContent(string $content): MessageBuilder
{
    return MessageBuilder::new()->setContent($content);
}

/**
 * Append to grab and empty array field. You can supply an embed to have the empty field added, or
 * if you leave the `$embed` option `null`, then an array containing the empty field will be returned
 *
 * ```php
 * $embed = newDiscordPart("\Discord\Parts\Embed\Embed");
 * emptyEmbedField($embed);
 * ```
 *
 * or
 *
 * ```php
 * $embed = newDiscordPart("\Discord\Parts\Embed\Embed");
 * $emptyField = emptyEmbedField();
 * ```
 */
function emptyEmbedField(?Embed $embed = null): array|Embed
{
    $emptyField = ['name' => "\u{200b}", 'value' => "\u{200b}"];

    if ($embed !== null) {
        return $embed->addField($emptyField);
    }

    return $emptyField;
}

/**
 * @template T
 *
 * @param class-string<T> $class
 *
 * @return T
 */
function newDiscordPart(string $class, mixed ...$args): mixed
{
    return new $class(discord(), ...$args);
}

/**
 * Quickly build an action row with multiple buttons
 *
 * ```php
 * $banButton = (new Button(Button::STYLE_DANGER))->setLabel("Ban User");
 * $kickButton = (new Button(Button::STYLE_DANGER))->setLabel("Kick User");
 * $actionRow = buildActionRowWithButtons($banButton, $kickButton);
 * ```
 *
 * *This can also be paired with newButton*
 *
 * ```php
 * $actionRow = buildActionWithButtons(
 *  newButton(Button::STYLE_DANGER, "Ban User")
 *  newButton(Button::STYLE_DANGER, "Kick User")
 * );
 * ```
 */
function buildActionRowWithButtons(Button ...$buttons): ActionRow
{
    $actionRow = new ActionRow();

    foreach ($buttons as $button) {
        $actionRow->addComponent($button);
    }

    return $actionRow;
}

/**
 * Quickly create button objects
 *
 * ```php
 * $button = newButton(Button::STYLE_DANGER, "Kick User", "Kick|Command_String");
 * ```
 */
function newButton(int $style, string $label, ?string $custom_id = null): Button
{
    return (new Button($style, $custom_id))->setLabel($label);
}

/**
 * Get an option from an Interaction/Interaction Repository by specifying the option(s) name
 *
 * For regular slash commands
 * `/ban :user`
 *
 * ```php
 * $user = getOptionFromInteraction($interaction, "user");
 * ```
 *
 * For sub commands / sub command groups you can stack the names
 * `/admin ban :user`
 *
 * ```php
 * $user = getOptionFromInteraction($interaction->data->options, "ban", "user");
 * ```
 */
function getOptionFromInteraction(Collection|Interaction $options, string ...$names): ?Option
{
    if ($options instanceof Interaction) {
        $options = $options->data->options;
    }

    $option = null;
    foreach ($names as $key => $name) {
        $option = $options->get('name', $name);

        if ($key !== count($names) - 1) {
            $options = $option?->options;
        }

        if (!isset($options, $option)) {
            break;
        }
    }

    return $option;
}

// Logging Functions

function log($level, string $message, array $context = []): void
{
    discord()?->getLogger()->log($level, $message, $context);
}

function debug(string $message, array $context = []): void
{
    discord()?->getLogger()->debug($message, $context);
}

function error(string $message, array $context = []): void
{
    discord()?->getLogger()->error($message, $context);
}

function info(string $message, array $context = []): void
{
    discord()?->getLogger()->info($message, $context);
}

// Internal Functions //

/**
 * Loop through all the classes in a directory and call a callback function with the class name
 */
function loopClasses(string $directory, callable $callback): void
{
    $convertPathToNamespace = static fn (string $path): string => str_replace([realpath(BOT_ROOT), '/'], ['', '\\'], $path);

    foreach (FileSystemUtils::getAllFiles($directory, true) as $file) {
        if (!str_ends_with($file, '.php')) {
            continue;
        }

        $className = basename($file, '.php');
        $path = dirname($file);
        $namespace = $convertPathToNamespace($path);
        $className = $namespace . '\\' . $className;

        $callback($className, $namespace, $file, $path);
    }
}

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
function doesClassHaveAttribute(string $class, string $attribute): object|false
{
    return (new ReflectionClass($class))->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF)[0] ?? false;
}

function deleteAllFilesInDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    foreach (FileSystemUtils::getAllFiles($directory) as $file) {
        unlink($file);
    }
}
