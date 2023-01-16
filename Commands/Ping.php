<?php

namespace Commands;

use Classes\Utils;
use Commands\Template;
use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class Ping extends Template {
    protected static string|array $name = "ping";

    public static function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('Pong :ping_pong:')->addComponent(self::getActionRow(0, false)), true);
    }

    public static function getActionRow(int $times, bool $ping): ActionRow
    {
        $button = ($ping) ? Utils::newButton(Button::STYLE_PRIMARY, "Ping", "Ping|$times") : Utils::newButton(Button::STYLE_SECONDARY, "Pong", "Pong|$times");

        return Utils::buildActionRowWithButtons($button);
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName(self::$name)
            ->setDescription("Ping the bot")
        ;
    }
}
