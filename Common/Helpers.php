<?php

namespace Common;

use CommandString\Env\Env;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Attachment;
use Discord\Parts\Interactions\Command\Option;

function newOption(string $name = "", string $description = ""): Option
{
    $option = newPartDiscord(Option::class);

    if (!empty($name)) {
        $option->setName($name);
    }

    if (!empty($description)) {
        $option->setDescription($description);
    }

    return $option;
}

function newPartDiscord(string $class): mixed
{
    return (new $class(Env::get()->discord));
}

function messageWithContent(string $content): MessageBuilder
{
    return MessageBuilder::new ()->setContent($content);
}

function appendErrorLog(string...$lines)
{
    $errorLogLocation = realpath(__DIR__ . "/../../error.log");

    $log = file_exists($errorLogLocation) ? file_get_contents($errorLogLocation) : "";

    foreach ($lines as $line) {
        file_put_contents($errorLogLocation, "$log" . PHP_EOL . "$line");
    }
}

function uploadedFileAttachment(string $fileName): Attachment
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