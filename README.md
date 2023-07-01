# DiscordPHP Bot Template
*this is still a work in progress*

# Table of Contents
* [Installation](#installation)
* [Configuration](#configuration)
* [Slash Commands](#slash-commands)
* [Events](#events)

# Installation

```
composer create-project commandstring/dphp-bot
```

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

#[Command('ping')]
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