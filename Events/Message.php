<?php

namespace Events;

use Core\Commands\DynamicCommand;
use Core\Events\MessageCreate;
use Core\Manager\CommandInstanceManager;
use Core\Manager\PrefixManager;
use Core\System;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Channel\Message as ChannelMessage;
use Discord\Parts\Embed\Embed;
use LogicException;
use Throwable;

use function Core\env;

class Message implements MessageCreate
{
    private $commandInstanceManager;

    private $commandPrefix;

    private $commandCollection;

    public function __construct()
    {
        $this->commandInstanceManager = new CommandInstanceManager();
        $this->commandPrefix = new PrefixManager(System::get()->db);
        $this->commandCollection = System::get()->cmdCollection;
    }

    public function handle(ChannelMessage $message): void
    {
        $this->commandInstanceManager->cleanupCommands();

        $guildId = $message->channel->guild_id;
        $prefix = $this->commandPrefix->getPrefix($guildId);

        if (strpos($message->content, $prefix) === 0) {
            $commandName = substr($message->content, strlen($prefix));

            try {
                $commandInstance = $this->handleCommand($message, $commandName);
            } catch (Throwable $e) {
                $this->handleError($e, $message);
            }

            if ($commandInstance !== null && $commandInstance instanceof DynamicCommand) {
                $this->commandInstanceManager->addCommand($commandInstance);
            }
        }
    }

    private function handleCommand(ChannelMessage $message, string $commandName): ?DynamicCommand
    {
        $command = $this->commandCollection->get($commandName);

        $instance = $command->instance->getInstance();

        if ($instance instanceof DynamicCommand) {
            $this->executeDynamicCommand($instance, $command->method, $message);

            return $instance;
        }

        $className = $command->class;
        $methodName = $command->method;

        if (method_exists($className, $methodName)) {
            $this->executeStaticCommand([$className, $methodName], $message);

            return null;
        } else {
            throw new LogicException("The defined method '$methodName' of class '$className' for the command '$commandName' does not exist");
        }
    }

    private function executeStaticCommand(callable $callable, ChannelMessage $message): void
    {
        $callable($message);
    }

    private function executeDynamicCommand(DynamicCommand $commandInstance, string $methodName, ChannelMessage $message): void
    {
        $commandInstance->$methodName($message);
    }

    private function handleError(Throwable $e, ChannelMessage $message): void
    {
        $discord = env()->discord;

        $embed = (new Embed($discord))
            ->setTitle('Exception Caught')
            ->setDescription('An exception occurred while processing your request.')
            ->setColor('#FF0000') // Set color to red using hexadecimal value
            ->setFooter($discord->username)
            ->setTimestamp()
            ->addField('Type', get_class($e))
            ->addField('Message', $e->getMessage())
            ->addField('File', $e->getFile())
            ->addField('Line', $e->getLine());

        $message->reply(MessageBuilder::new()->addEmbed($embed));
    }
}
