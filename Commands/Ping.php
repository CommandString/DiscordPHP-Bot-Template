<?php

namespace Commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

use function Common\newButton;
use function Common\buildActionRowWithButtons;

class Ping extends BaseCommand
{
    protected static string|array $name = "ping";

    public static function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()
            ->setContent('Pong :ping_pong:')
            ->addComponent(self::getActionRow(0, false)), true)
        ;
    }

    public static function getActionRow(int $times, bool $ping): ActionRow
    {
        $button = ($ping) ?
        newButton(Button::STYLE_PRIMARY, "Ping", "Ping|{$times}") :
        newButton(Button::STYLE_SECONDARY, "Pong", "Pong|{$times}");

        return buildActionRowWithButtons($button);
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder())
            ->setName(self::$name)
            ->setDescription("Ping the bot")
        ;
    }
}
