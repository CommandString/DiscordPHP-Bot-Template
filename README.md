
# DiscordPHP Bot Template

The unofficial DiscordPHP Bot boilerplate

# Table of contents
[Important Resources](#important-resources)

[Environment Configuration](#environment-configuration)

[Events](#events)

[Slash Commands](#slash-commands)

[Interactions](#interactions)

[Using example](#using-the-example)

[Helper Functions](#helper-functions)

# Important Resources #

[DiscordPHP Class Reference](https://discord-php.github.io/DiscordPHP/guide/)

[DiscordPHP Documentation](https://discord-php.github.io/DiscordPHP/)

[DiscordPHP Discord Server](https://discord.gg/kM7wrJUYU9)
*Only ask questions relevant to using DiscordPHP's own wrapper, not on how to use this.*

# Environment Configuration #

1. Copy `.env.example` to `.env`.
2. Open `.env` and add your bot token, you can also add additional environment variables that pertain to your bot. Such as mysql credentials.

### Retrieving Environment Variables ###

```php
Env::get()->variableName;
```

### Adding Environment Variables at Runtime ###

```php
Env::get()->variableName = "value";
```

### Dedicated Env function

```php
use function Common\env;

Env::get() === env(); // these do the same thing
```

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
4. If your command has autocomplete enabled then put the code relevant to that inside the autocomplete method (if not you can remove the autocomplete method)
5. If your command is guild specific then you can change the static property `$guild` to the id of the guild.
After completing the steps above you should be left with something similar to...
```php
<?php

namespace Commands;

use Commands\Template;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

class Ping extends Template {
    protected static string|array $name = "ping";

    public static function handler(Interaction $interaction): void
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('pong :ping_pong:'), true);
    }

    public static function getConfig(): CommandBuilder|array
    {
        return (new CommandBuilder)
            ->setName(self::$name)
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

`php bot sc save commandName`

### Deleting Commands From Your Application ###

Similar to adding and updating commands to your application you can just swap save with delete.

`php bot sc delete commandName`

### Deleting All Commands From Your Application ###

`php bot sc deleteall`

### Using sc comamnd for commands in a subdirectory?

If your command's path, for example, is `Commands/Admin/Ban.php` instead of doing `php bot sc action Ban` you would do `php bot sc action Admin\\Ban`

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
        Commands\BaseCommand::class,
        Commands\SubCommand::class
        // ...
    ];
}
```

**If you do not do this the SubCommand's handler will be used for the baseCommand!!**

# Interactions

### Creating Interactions

By default you would have to create an event listener for the INTERACTION_CREATE event and then most likely do a bunch of if-else statements to define your custom handlers. For organization I created an easier way to listen for interactions namespace just like you would regular events.

1. Copy `Interactions/Example.php` to `Interaction/NameOfYourInteraction.php`
2. Change the static property `$id` to the name custom_id of your interaction
3. If you want your Interaction to run more than once you can change the runOnce property to true (you can also remove this property definition if not)
4. Added the code you want executed whenever this interaction is triggered within the static handler method. Afterwards you should be left with something like
```php
<?php

namespace Interactions;

use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Discord;

class Pong extends Template {
    protected static string $id = "Pong";

    public static function handler(Interaction $interaction, Discord $discord)
    {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('Ping :ping_pong:')->addComponent(\Commands\Ping::getActionRow()), true);
    }
}
```

### Storing data in the id

If you need to store data in the id, separate the id and the data e.g. `Ping|5` make sure the id comes first. You can then access these by adding optional args after Discord $discord in the handler method. Also, args are passed in the same order as defined in the id.

```php
// ...
public static function handler(Interaction $interaction, Discord $discord, int $timesPinged)
// ...
```

You can have multiple columns of data if needed as well. e.g. `Ping|5|232224992908017664`

```php
// ...
public static function handler(Interaction $interaction, Discord $discord, int $timesPinged, string $userid)
// ...
```

### Listening for interactions

On `index.php` add the interaction classname to `$env->interactions` like so...

```php
$env->interactions = [
    Interactions\Ping::class,
    Interactions\Pong::class
];
```

# Using the example
`composer install`

`php bot s` (s is an alias for setup)

Enter token

`php bot sc save Ping` (sc is an alias for slashcommands)

`php index.php`

# Helper Functions

Inside `Common/Helpers.php` there's a bunch of utility commands that can make some repetitive tasks easier

[getDiscord](#getDiscord)

[emptyEmbedField](#emptyEmbedField)

[getOptionFromInteraction](#getOptionFromInteraction)

[newButton](#newButton)

[buildActionRowWithButtons](#buildActionRowWithButtons)

[messageWithContent](#messageWithContent)

[newDiscordPart](#newDiscordPart)

[newSlashCommandChoice](#newSlashCommandChoice)

[newSlashCommandOption](#newSlashCommandOption)


# newSlashCommandOption

## Header

```php
function newSlashCommandOption(string $name, string $description, int $type, bool $required = false): Option
```

## Arguments

| Type | Name |
|------|------|
|string|$name| 
|string|$description| 
|int|$type| 
|bool|$required| 

## Return Type
`Option`

## Description
Create a new Option used for building slash commands

# newSlashCommandChoice

## Header

```php
function newSlashCommandChoice(string $name, float|int|string $value): Choice
```

## Arguments

| Type | Name |
|------|------|
|string|$name| 
|float|int|string|$value| 

## Return Type
`Choice`

## Description
Create a new Choice used for building slash commands

# newDiscordPart

## Header

```php
function newDiscordPart(string $class, mixed ...$args): mixed
```

## Arguments

| Type | Name |
|------|------|
|string|$class| 
|mixed|...$args| 

## Return Type
`mixed`

## Description
Create a new instance of an object that requires `\Discord\Discord` as the first argument

```php
$embed = newDiscordPart("\Discord\Parts\Embed\Embed);
```

# messageWithContent

## Header

```php
function messageWithContent(string $content): MessageBuilder
```

## Arguments

| Type | Name |
|------|------|
|string|$content| 

## Return Type
`MessageBuilder`

## Description
Create a new MessageBuilder object with the content define for creating simple MessageBuilders quickly

```php
$message = messageWithContent("Hello World");
```

# buildActionRowWithButtons

## Header

```php
function buildActionRowWithButtons(Button ...$buttons): ActionRow
```

## Arguments

| Type | Name |
|------|------|
|Button|...$buttons| 

## Return Type
`ActionRow`

## Description
Quickly build an action row with multiple buttons

```php
$banButton = (new Button(Button::STYLE_DANGER))->setLabel("Ban User");
$kickButton = (new Button(Button::STYLE_DANGER))->setLabel("Kick User");
$actionRow = buildActionRowWithButtons($banButton, $kickButton);
```

*This can also be paired with newButton*

```php
$actionRow = buildActionWithButtons(
 newButton(Button::STYLE_DANGER, "Ban User")
 newButton(Button::STYLE_DANGER, "Kick User")
);
```

# newButton

## Header

```php
function newButton(int $style, string $label, ?string $custom_id = null): Button
```

## Arguments

| Type | Name |
|------|------|
|int|$style| 
|string|$label| 
|?string|$custom_id| 

## Return Type
`Button`

## Description
Quickly create button objects

```php
$button = newButton(Button::STYLE_DANGER, "Kick User", "Kick|Command_String");
```

# getOptionFromInteraction

## Header

```php
function getOptionFromInteraction(Collection|Interaction $options, string ...$names): Option|null
```

## Arguments

| Type | Name |
|------|------|
|Collection|Interaction|$options| 
|string|...$names| 

## Return Type
`Option|null`

## Description
Get an option from an Interaction/Interaction Repository by specifying the option(s) name

For regular slash commands
`/ban :user`

```php
$user = getOptionFromInteraction($interaction, "user");
```

For sub commands / sub command groups you can stack the names
`/admin ban :user`

```php
$user = getOptionFromInteraction($interaction->data->options, "ban", "user");
```

# emptyEmbedField

## Header

```php
function emptyEmbedField(?Embed $embed = null): array|Embed
```

## Arguments

| Type | Name |
|------|------|
|?Embed|$embed| 

## Return Type
`array|Embed`

## Description
Append to grab and empty array field. You can supply an embed to have the empty field added or
if you leave the `$embed` option `null` then an array containing the empty field will be returned

```php
$embed = newDiscordPart("\Discord\Parts\Embed\Embed");
emptyEmbedField($embed);
```

or

```php
$embed = newDiscordPart("\Discord\Parts\Embed\Embed");
$emptyField = emptyEmbedField();
```

# getDiscord

## Header

```php
function getDiscord(): Discord
```


## Return Type
`Discord`

## Description
Retrieve the `\Discord\Discord` instance from Environment
