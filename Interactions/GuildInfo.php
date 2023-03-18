<?php

namespace Interactions;

use Commands\Guild as GuildCommand;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Guild\Emoji;

use function Common\messageWithContent;
use function Common\newDiscordPart;

class GuildInfo extends BaseInteraction
{
    protected static string $id = "GuildInfo";

    public static function handler(Interaction $interaction, Discord $discord, int $actionId = 0)
    {
        /** @var Embed $embed */
        if ($actionId < 1 || $actionId > 2) {
            $interaction->respondWithMessage(messageWithContent("Invalid Action"), true);
            return;
        }

        $msg = new MessageBuilder();
        $embed = newDiscordPart(Embed::class);
        $guild = $interaction->guild;

        $embed->setAuthor($guild->name, $guild->icon, $guild->invites->first());

        if ($actionId === GuildCommand::VIEW_EMOJIS) {
            /** @var Emoji $emoji */
            $emojis = $guild->emojis;

            $emojiString = "";

            foreach ($emojis as $emoji) {
                $emojiString .= "`{$emoji}` {$emoji}\n";
            }

            $embed->setTitle("{$guild->name} Emojis")->setDescription($emojiString);
        } elseif ($actionId === GuildCommand::VIEW_ICON) {
            $embed->setTitle("{$guild->name} Icon")->setImage($guild->icon);
        }

        $msg->addEmbed($embed);

        $interaction->respondWithMessage($msg, true);
    }
}
