# DiscordPHP Bot Template

An unofficial way to structure a discordPHP bot.

# Table of Contents

* [Installation](#installation)
* [Important Resources](#important-resources)
* [Configuration](#configuration)
* [Slash Commands](#slash-commands)
* [Events](#events)
* [Disabling Commands and Events](#disabling-commands-and-events)
* [Extending The Template](#extending-the-template)
  * [Bootstrap Sequence](#bootstrap-sequence)
  * [Environment Variables](#environment-variables)

# Installation

```
composer create-project commandstring/dphp-bot
```

# Important Resources #

[DiscordPHP Class Reference](https://discord-php.github.io/DiscordPHP/guide/)

[DiscordPHP Documentation](https://discord-php.github.io/DiscordPHP/)

[DiscordPHP Discord Server](https://discord.gg/kM7wrJUYU9)
*Only ask questions relevant to DiscordPHP's own wrapper, not on how to use this.*

[Developer Hub](https://discord.gg/TgrcSkuDtQ) *Issues about this template can be asked here*

# Configuration

Copy the `.env.example` file to `.env` and add your bot token.

# Slash Commands

Create a class that implements `Core\Commands\CommandHandler` and attach the `Core\Commands\Command` attribute to it.

```php
<?php

namespace Commands;

use Core\Commands\Command;
use Core\Commands\CommandHandler;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Interaction;

use function Core\messageWithContent;

#[Command]
class Ping implements CommandHandler
{
    public function handle(Interaction $interaction): void
    {
        $interaction->respondWithMessage(messageWithContent('Ping :ping_pong:'), true);
    }

    public function autocomplete(Interaction $interaction): void
    {
    }

    public function getConfig(): CommandBuilder
    {
        return (new CommandBuilder())
            ->setName('ping')
            ->setDescription('Ping the bot');
    }
}
```

Once you start the bot, it will automatically register the command with Discord.
And if you make any changes to the config, it will update the command on the fly.

# Events

Create a class that implements any of the interfaces found inside of `Core\Events`.
Implement the interface that matches your desired event.

```php
<?php

namespace Events;

use Core\Events\Init;
use Discord\Discord;

class Ready implements Init
{
    public function handle(Discord $discord): void
    {
        echo "Bot is ready!\n";
    }
}
```

# Disabling Commands and Events

If you want to disable a command handler or event listener attach the `Core\Commands\Disabled` attribute to it.

```php
<?php

namespace Events;

use Core\Events\Init;
use Discord\Discord;

#[Disabled]
class Ready implements Init
{
    public function handle(Discord $discord): void
    {
        echo "Bot is ready!\n";
    }
}
```

# Extending The Template

## Bootstrap Sequence

Create a file inside `/Bootstrap` and then require it inside of `/Boostrap/Requires.php`.

## Environment Variables

Add a doc comment to `/Core/Env.php` and then add the variable to `.env`

*You should also add it to `.env.example`*
