<?php

namespace Common;

use CommandString\Env\Env;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Attachment;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Choice;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Interactions\Request\Option as RequestOption;

function newOption(string $name, string $description, int $type, bool $required = false): Option
{
    return newPartDiscord(Option::class)
        ->setName($name)
        ->setDescription($description)
        ->setType($type)
        ->setRequired($required)
    ;
}

function newChoice(string $name, float|int|string $value): Choice
{
    return newPartDiscord(Choice::class)
        ->setName($name)
        ->setValue($value)
    ;
}

function newPartDiscord(string $class, mixed ...$args): mixed
{
    return (new $class(Env::get()->discord, ...$args));
}

function messageWithContent(string $content): MessageBuilder
{
    return MessageBuilder::new()->setContent($content);
}

function createLocalFileAttachment(string $fileName): Attachment
{
    return new Attachment(Env::get()->discord, [
        "filename" => $fileName
    ]);
}

function buildActionRowWithButtons(Button ...$buttons): ActionRow
{
    $actionRow = new ActionRow();
    
    foreach ($buttons as $button) {
        $actionRow->addComponent($button);
    }

    return $actionRow;
}

function newButton(int $style, string $label, ?string $custom_id = null): Button
{
    return (new Button($style, $custom_id))->setLabel($label);
}

function getOptionFromInteraction(Collection|Interaction $options, string ...$names): ?RequestOption
{
    if ($options instanceof Interaction) {
        $options = $options->data->options;
    }

    foreach ($names as $key => $name) {
        $option = $options->get("name", $name);

        if ($key !== count($names)-1) {
            $options = $option?->options;
        }

        if (is_null($options) || is_null($option)) {
            break;
        }
    }

    return $option;
}

function emptyEmbedField(?Embed $embed = null): array|Embed
{
    $emptyField = ["name" => "\u{200b}", "value" => "\u{200b}"];

    if (!is_null($embed)) {
        return $embed->addField($emptyField);
    } else {
        return $emptyField;
    }
}