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
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('pong :ping_pong:')->addComponent(self::getActionRow()), true);
    }

    public static function getActionRow(): ActionRow
    {
        return Utils::buildActionRowWithButtons(Utils::newButton(Button::STYLE_PRIMARY, "Ping", "Ping"), Utils::newButton(Button::STYLE_SECONDARY, "Pong", "Pong"));
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName(self::$name)
            ->setDescription("Ping the bot")
        ;
    }
}
