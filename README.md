
# DiscordPHP Design Structure
This repo is designed to help design a structure for DiscordPHP bots.

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
Config::get()->variableName;
```

### Adding Environment Variables at Runtime ###
```php
Config::get()->variableName = "value";
```

### Important Notes About Environment Variables ###
* Environment variables are treated similarly to readonly properties

# Events #
### Creating Events ###
1. Create a new PHP script inside `./custom/Events/` named after the name of your event. Then copy the code below into it
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
1. Create a new PHP script inside `./custom/Commands/` named after the name your Command then copy the code below into it.
	```php
	<?php

	namespace Discord\Bot\Commands;

	use Discord\Builders\CommandBuilder;
	use Discord\Parts\Interactions\Interaction;

	/**
	 * @inheritDoc CommandTemplate
	 */
	class CommandName extends Template {
	    public function handler(Interaction $interaction): void
	    {
	    }
		
	    public function autocomplete(Interaction $interaction): void
	    {
	    }

	    public function getName(): string
	    {
	        return "CommandName";
	    }

	    public function getConfig(): CommandBuilder|array
	    {
	        return (new CommandBuilder)
	            ->setName($this->name)
	            ->setDescription("CommandDescription");
	    }

	    public function getGuild(): string
	    {
	        return "";
	    }
	}
	```
2. Replace `CommandName` with your command name and `CommandDescription` with the description of your command. 
3. Add the code that will be invoked inside your `handler` method and add any additional command configuration required into the `getConfig` method. *Advance users can also return an array rather than using the CommandBuilder*.
4. *If your command has autocomplete enabled then put the code relevant to that inside the autocomplete method*
5. If your command is guild specific then you can add the id of the guild inside the `getGuild` method, if not leave the return as an empty string.
After completing the steps above you should be left with something similar to...
```php
<?php

namespace Discord\Bot\Commands;

use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

/**
 * @inheritDoc CommandTemplate
 */
class Ping extends Template {
    public function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('pong!'));
    }
		
	public function autocomplete(Interaction $interaction): void
	{
	}

    public function getName(): string
    {
        return "ping";
    }

    public function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName($this->getName())
            ->setDescription("Ping the bot")
            ->setDefaultMemberPermissions(0);
    }

    public function getGuild(): string
    {
        return "";
    }
}
```

### Adding & Updating Commands in Your Application ###
You can use the PHP script below add a command to your application or update an existing one.
`php command save commandName`

*You can list as many commands as you'd like after the action*

### Deleting Commands From Your Application ###
Similar to adding and updating commands to your application you can just swap save with delete.
`php command delete commandName`

*Multiple command names can be specified here as well*

### Deleting All Commands From Your Application ###
`php command delete all`

### Listening for Commands ###
Inside the already created `ready event` handler, `./custom/Events/ready.php`, create an anonymous class for your command and invoke it's `listen` method.
```php 
use Discord\Bot\Commands\Ping;
// ...
public function handler(Discord $discord = null): void
{
    (new Ping)->listen();
}
// ...
```