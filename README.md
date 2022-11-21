
# DiscordPHP Bot Template
This repo is a boiler plate for discord bots made in PHP :)

# Important Resources #
[DiscordPHP Class Reference](https://discord-php.github.io/DiscordPHP/guide/)

[DiscordPHP Documentation](https://discord-php.github.io/DiscordPHP/)

[DiscordPHP Discord Server](https://discord.gg/kM7wrJUYU9)
*Only ask questions relevant to using DiscordPHP's own wrapper, not on how to use this.*

# Environment Configuration #
1. `php index.php` an exception should be thrown asking you configure `env.json`.
2. Open `env.json` and add your bot token, you can also add additional environment variables that pertain to your bot. Such as mysql credentials.

### Retrieving Environment Variables ###
```php
Env::get()->variableName;
```

### Adding Environment Variables at Runtime ###
```php
Env::get()->variableName = "value";
```

### Important Notes About Environment Variables ###
* Environment variables are treated similarly to readonly properties

# Events #
### Creating Events ###
1. Create a new PHP script inside `custom/Events/` named after the name of your event. Then copy the code below into it
	```php
	<?php

	namespace Discord\Bot\Events;

	use Discord\Discord;
	use Discord\WebSockets\Event;

	/**
	 * @inheritDoc Template
	 */
	class EVENT_NAME extends Template {
	    public function handler(): void
	    {
	    }
	  
	    public function getEvent(): string
	    {
	        return EVENT::EVENT_NAME;
	    }

	    public function runOnce(): bool
	    {
	        return false;
	    }
	}
	```
3. replace `EVENT_NAME` with the name of your event
4. Insert the code you'd like to run when that event is triggered into the handler method. When defining arguments that are passed into the event handler make sure to set their default value to null. *This is due to how extending abstract classes work as you cannot have required arguments that aren't defined in the parent class.*
5. If you want your event handler to only run once then change the return from `false` to `true` in the `runOnce` method

After following the steps above you should be left with something that looks like this.
```php
<?php

namespace Discord\Bot\Events;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

/**
 * @inheritDoc Template
 */
class MESSAGE_CREATE extends Template {
    public function handler(Message $message = null, Discord $discord = null): void
    {
        if ($message->author->bot) {
            return;
        }

        $message->reply("Well I can't read what you said but I'm glad you said something :)");
    }
  
    public function getEvent(): string
    {
        return Event::MESSAGE_CREATE;
    }

    public function runOnce(): bool
    {
        return false;
    }
}
```

### Listening for Events ###
Inside `index.php`, instantiate an anonymous class and invoke it's `listen` method
```php
use Discord\Bot\Events\MESSAGE_CREATE;
// ...
(new MESSAGE_CREATE)->listen();
// ...
```

# Slash Commands #

### Creating Commands ###
1. Copy `custom/Commands/Examples/Example.php` to where you want it and name it after the name of your command; For example I'd copy it to `custom/Commands/Examples/Ping.php`
2. Replace `Example` with your command name (for subcommands check [Additional notes for subcommands](#additional-notes-for-subcommands)) and `Example Command` with the description of your command. 
3. Add the code that will be invoked inside your `handler` method and add any additional command configuration required into the `getConfig` method. *Advance users can also return an array rather than using the CommandBuilder*.
4. *If your command has autocomplete enabled then put the code relevant to that inside the autocomplete method*
5. If your command is guild specific then you can add the id of the guild inside the `getGuild` method, if not leave the return as an empty string.
After completing the steps above you should be left with something similar to...
```php
<?php

namespace Discord\Bot\Commands\Examples;

use Discord\Bot\Commands\Template;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class Ping extends Template {
    public function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('pong :ping_pong:'));
    }

    public function autocomplete(Interaction $interaction): void
    {
        
    }

    public function getName(): string
    {
        return "ping";
    }

    public function getConfig(): CommandBuilder
    {
        return (new CommandBuilder)
            ->setName($this->getName())
            ->setDescription("Ping the bot")
            ->setDefaultMemberPermissions(0)
        ;
    }

    public function getGuild(): string
    {
        return "";
    }
}
```

### Additional notes for subcommands

If you're command has subcommands change your `getName()` method to look more like...

```php
// ...
	public function getName(): array
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
	public function getConfig(): CommandBuilder
    {
        return (new CommandBuilder)
            ->setName($this->getName()[0]) // <-- gets base command name
            // rest of the config can stay the same
```

**An example of this can be found in `custom/Commands/Examples/Randomize.php`**

### Want different handlers for each subcommand?

Do not include the subcommand name in the base command class instead create another command class like you would for any other command and the array return in the `getName()` method will be...

```php
["baseCommandName", ["subCommandName"]]
```

You can also change the `getConfig()` method to...

```php
	public function getConfig(): array
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

**An example can be found in `custom/Commands/Examples/Up.php`**

### Adding & Updating Commands in Your Application ###

You can use the PHP script below to add a command to your application or update an existing one.

`php command save commandName`

*You can list as many commands as you'd like after the action*

### Deleting Commands From Your Application ###

Similar to adding and updating commands to your application you can just swap save with delete.

`php command delete commandName`

*Multiple command names can be specified here as well*

### Deleting All Commands From Your Application ###

`php command delete all`

### Using command for commands in a subdirectory?

If your command's path, for example, is `custom/Commands/Admin/Ban.php` instead of doing `php action Ban` you would do `php action Admin\\Ban`

*Note the namespace in Ban.php would have to be `Discord\Bot\Commands\Admin` to work!*

### Listening for Commands ###

Inside the already created `ready event` handler, `custom/Events/ready.php`, create an anonymous class for your command and invoke it's `listen` method.

```php 
use Discord\Bot\Commands\Ping;
// ...
public function handler(Discord $discord = null): void
{
    (new Ping)->listen();
}
// ...
```

**SPECIAL NOTE: If you have a subcommand that has a different handler than the base command make sure you add it's listener second, for example**

```php
public function handler(Discord $discord = null): void
{	
    (new BaseCommand)->listen();
	(new SubCommand)->listen();
}
```

*If you do not do this the SubCommand's handler will be used for the baseCommand!!*

# Need additional assistance?

I've created a discord server that you can join if you have any trouble setting up this template!

[Command's Dev Server](https://discord.gg/TgrcSkuDtQ) 

*I'll find a better name eventually XD*