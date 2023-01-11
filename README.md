
# DiscordPHP Bot Template
This repo is a boiler plate for discord bots made in PHP :)

# Important Resources #
[DiscordPHP Class Reference](https://discord-php.github.io/DiscordPHP/guide/)

[DiscordPHP Documentation](https://discord-php.github.io/DiscordPHP/)

[DiscordPHP Discord Server](https://discord.gg/kM7wrJUYU9)
*Only ask questions relevant to using DiscordPHP's own wrapper, not on how to use this.*

# Environment Configuration #
1. Copy `env.example.json` to `env.json`.
2. Open `env.json` and add your bot token, you can also add additional environment variables that pertain to your bot. Such as mysql credentials.

### Retrieving Environment Variables ###
```php
Env::get()->variableName;
```

### Adding Environment Variables at Runtime ###
```php
Env::get()->variableName = "value";
```

*You can read more at [CommandString/Env](https://github.com/commandstring/env)*

**NOTE: Environment variables are readonly properties**

# Events #
### Creating Events ###
1. Copy `Events/Example.php` to `Events/NameOfYourEvent.php`
2. Replace `Event::MESSAGE_CREATE` with the name of your event
3. Insert the code you'd like to run when that event is triggered into the handler method. When defining arguments that are passed into the event handler make sure to set their default value to null. *This is due to how extending abstract classes work as you cannot have required arguments that aren't defined in the parent class.*
4. If you want your event handler to only run once then change the `$runOnce` property to `true`

After following the steps above you should be left with something that looks like this.
```php
<?php

namespace Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

class MESSAGE_CREATE extends Template {
    protected static string $event = Event::MESSAGE_CREATE;
    protected static bool $runOnce = false;

    public static function handler(Message $message = null, Discord $discord = null): void
    {
        if ($message->author->bot) {
            return;
        }

        $message->reply("Well I can't read what you said but I'm glad you said something :)");
    }
}
```

### Listening for Events ###
Inside `index.php`, add the class name to the `$env->events` array like so...
```php
// ...
$env->events = [
    Events\Ready::class, // DO NOT REMOVE THIS EVENT!
    Events\MESSAGE_CREATE::class
];
// ...
```

# Slash Commands #

### Creating Commands ###
1. Copy `Commands/Example.php` to `Commands/NameOfYourCommand.php`.
2. Replace `Example` with your command name (for subcommands check [Additional notes for subcommands](#additional-notes-for-subcommands)) and `Example Command` with the description of your command. 
3. Add the code that will be invoked inside your `handler` method and add any additional command configuration required into the `getConfig` method. *Advance users can also return an array rather than using the CommandBuilder*.
4. *If your command has autocomplete enabled then put the code relevant to that inside the autocomplete method*
5. If your command is guild specific then you can add the id of the guild inside the `getGuild` method, if not leave the return as an empty string.
After completing the steps above you should be left with something similar to...
```php
<?php

namespace Commands;

use Commands\Template;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class Ping extends Template {
    public static function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('pong :ping_pong:'), true);
    }

    public static function getName(): string
    {
        return "ping";
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName(self::getName())
            ->setDescription("Ping the bot")
        ;
    }
}
```

### Additional notes for subcommands

If you're command has subcommands change your `getName()` method to look more like...

```php
// ...
	public static function getName(): array
	{
		return ["baseCommandName", ["subCommandName"], ["secondSubCommandName"]]
	}
// ...
```

*For subcommand groups your array would look like this*
```php
["baseCommandName", ["subCommandGroupName", "subCommandName"], ["subCommandGroupName", "secondSubCommandName"]]
```

You will also need to modify your `getConfig()` method to look like...

```php
// ...
	public static function getConfig(): CommandBuilder
    {
        return (new CommandBuilder)
            ->setName(self::getName()[0]) // <-- gets base command name
            // rest of the config can stay the same
```

### Want different handlers for each subcommand?

Do not include the subcommand name in the base command class instead create another command class like you would for any other command and the array return in the `getName()` method will be...

```php
["baseCommandName", ["subCommandName"]]
```

You can also change the `getConfig()` method to...

```php
	public static function getConfig(): array
	{
		return [];
	}
```

As this method will never be called

*Note you will see a warning in the console like this...*
```
Warning caught: The command `{insert your baseCommandName here}` already exists.
If this is about a command already existing for a command you're listening for that has a separate subcommand handler you can safely ignore this :)
```

### Adding & Updating Commands in Your Application ###

You can use the PHP script below to add a command to your application or update an existing one.

`php command save commandName`

### Deleting Commands From Your Application ###

Similar to adding and updating commands to your application you can just swap save with delete.

`php command delete commandName`

### Deleting All Commands From Your Application ###

`php command delete all`

### Using command for commands in a subdirectory?

If your command's path, for example, is `Commands/Admin/Ban.php` instead of doing `php action Ban` you would do `php action Admin\\Ban`

*Note the namespace in Ban.php would have to be `Commands\Admin` to work!*

### Listening for Commands ###

In `index.php` there is environment variable `commands` that's an array. Add the class name of your command like so...

```php
// ...
$env->commands = [
    Commands\Ping::class,
    Commands\Profile::class,
    // ...
];
```

**SPECIAL NOTE: If you have a subcommand that has a different handler than the base command make sure you add it's listener second, for example**

```php
public function handler(Discord $discord = null): void
{	
    $env->commands = [
        Commands\Ping::class,
        Commands\Profile::class,
        Commands\BaseCommand::class;
	    Commands\SubCommand::class
        // ...
    ];
}
```

**If you do not do this the SubCommand's handler will be used for the baseCommand!!**

# Need additional assistance?

I've created a discord server that you can join if you have any trouble setting up this template!

[Command's Dev Server](https://discord.gg/TgrcSkuDtQ) 

*I'll find a better name eventually XD*