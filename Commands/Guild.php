<?php

namespace Commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;

use function Common\buildActionRowWithButtons;
use function Common\newButton;
use function Common\newDiscordPart;

class Guild extends BaseCommand
{
    public const VIEW_EMOJIS = 1;
    public const ICON_URL = 2;
    public const LIST_MEMBERS = 3;

    protected static array|string $name = 'guild';

    public static function handler(Interaction $interaction): void
    {
        /** @var Embed $embed */
        /** @var User $owner */
        $guild = $interaction->guild;
        $embed = newDiscordPart(Embed::class);
        $msg = new MessageBuilder();


        if ($owner = $guild->owner) {
            $embed->addFieldValues('Owner', $owner);
        }

        $embed
            ->setAuthor($guild->name, null)
            ->setThumbnail($guild->icon)
            ->setDescription($guild->description ?? "")
            ->addFieldValues('Member Count', $guild->member_count, true)
            ->addFieldValues('Channel Count', $guild->channels->count(), true)
            ->addFieldValues("Emoji Count", $guild->emojis->count(), true)
            ->addFieldValues("Snowflake", "`{$guild->id}`", true)

            ->setFooter("Created ")
            ->setTimestamp($guild->createdTimestamp())
        ;
        $msg->addEmbed($embed);

        $newButton = fn(string $action, string $actionId) => newButton(Button::STYLE_PRIMARY, $action, "GuildInfo|{$actionId}");

        $msg->addComponent(
            buildActionRowWithButtons(
                $newButton("Icon URL", self::ICON_URL),
                $newButton("View Emojis", self::VIEW_EMOJIS),
                $newButton("List Members", self::LIST_MEMBERS),
            )
        );

        $interaction->respondWithMessage($msg, true);
    }

    public static function autocomplete(Interaction $interaction): void
    {
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder())
            ->setName(self::getBaseCommandName())
            ->setDescription('Get info about the current guild')
        ;
    }
}
