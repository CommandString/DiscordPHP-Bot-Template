<?php

namespace Common;

use CommandString\Env\Env;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Choice;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;

/**
 * Create a new Option used for building slash commands
 */
function newSlashCommandOption(string $name, string $description, int $type, bool $required = false): Option
{
    return newDiscordPart(Option::class)
        ->setName($name)
        ->setDescription($description)
        ->setType($type)
        ->setRequired($required)
    ;
}

/**
 * Create a new Choice used for building slash commands
 */
function newSlashCommandChoice(string $name, float|int|string $value): Choice
{
    return newDiscordPart(Choice::class)
        ->setName($name)
        ->setValue($value)
    ;
}

/**
 * Create a new instance of an object that requires `\Discord\Discord` as the first argument
 *
 * ```php
 * $embed = newDiscordPart("\Discord\Parts\Embed\Embed);
 * ```
 */
function newDiscordPart(string $class, mixed ...$args): mixed
{
    return (new $class(Env::get()->discord, ...$args));
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
function getOptionFromInteraction(Collection|Interaction $options, string ...$names): Option|null
{
    if ($options instanceof Interaction) {
        $options = $options->data->options;
    }

    $option = null;
    foreach ($names as $key => $name) {
        $option = $options->get("name", $name);

        if ($key !== count($names) - 1) {
            $options = $option?->options;
        }

        if ($options === null || $option === null) {
            break;
        }
    }

    return $option;
}

/**
 * Append to grab and empty array field. You can supply an embed to have the empty field added or
 * if you leave the `$embed` option `null` then an array containing the empty field will be returned
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
    $emptyField = ["name" => "\u{200b}", "value" => "\u{200b}"];

    if ($embed !== null) {
        return $embed->addField($emptyField);
    }

    return $emptyField;
}

/**
 * Retrieve the `\Discord\Discord` instance from Environment
 */
function getDiscord(): Discord
{
    return Env::get()->discord;
}