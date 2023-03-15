<?php

namespace Commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;
use Throwable;

use function Common\buildActionRowWithButtons;
use function Common\emptyEmbedField;
use function Common\getDiscord;
use function Common\newButton;
use function Common\newDiscordPart;
use function React\Async\await;

class Guild extends BaseCommand
{
    public const VIEW_EMOJIS = 1;
    public const VIEW_ICON = 2;

    protected static array|string $name = 'guild';

    public static function handler(Interaction $interaction): void
    {
        /** @var Embed $embed */
        /** @var User $owner */
        $guild = $interaction->guild;
        $embed = newDiscordPart(Embed::class);
        $msg = new MessageBuilder;


        if ($owner = $guild->owner) {
            $embed->addFieldValues('Owner', $owner);
        }

        $embed
            ->setAuthor($guild->name, $guild->icon, $guild->invites->first())
            ->setDescription($guild->description ?? "")
            ->addFieldValues('Member Count', $guild->member_count, true)
            ->addFieldValues('Channel Count', $guild->channels->count(), true)
            ->addFieldValues("Emoji Count", $guild->emojis->count(), true)
            ->addFieldValues("Snowflake", $guild->id, true)

            ->setFooter("Created ")
            ->setTimestamp($guild->createdTimestamp())
        ;
        $msg->addEmbed($embed);

        $newButton = fn(string $action, string $actionId) => newButton(Button::STYLE_PRIMARY, $action, "GuildInfo|{$actionId}");

        $msg->addComponent(
            buildActionRowWithButtons(
                $newButton("View Icon", self::VIEW_ICON),
                $newButton("View Emojis", self::VIEW_EMOJIS),
            )
        );

        $interaction->respondWithMessage($msg);
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
