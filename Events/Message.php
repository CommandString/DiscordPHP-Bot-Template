<?php

namespace Events;

use Core\Commands\DynamicCommand;
use Core\Events\MessageCreate;
use Core\Manager\CommandExpirationManager;
use Core\Env;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message as ChannelMessage;
use Discord\Parts\Embed\Embed;
use LogicException;
use Throwable;

use function Core\env;
use function core\discord as d;

class Message implements MessageCreate
{
    private $commandExpirationManager;
    private $commandPrefix;
    private $messageCommandRepository;

    public function __construct()
    {
        $this->commandExpirationManager = new CommandExpirationManager(d()->getLoop());
        $this->commandPrefix = Env::get()->prefixManager;
        $this->messageCommandRepository = Env::get()->messageCommandRepository;
    }

    public function handle(ChannelMessage $message, Discord $discord): void
    {

        $guildId = $message->channel->guild_id;
        $prefix = $this->commandPrefix->getPrefix($guildId);

        if (strpos($message->content, $prefix) !== 0) {
            return;
        }

        $message->content = substr($message->content, strlen($prefix));
        try {
            $commandInstance = $this->handleCommand($message, $message->content);
        } catch (Throwable $e) {
            self::handleError($e, $message);
        }

        if ($commandInstance instanceof DynamicCommand) {
            $this->commandExpirationManager->addCommand($commandInstance);
        }
    }

    private function handleCommand(ChannelMessage $message, string $commandName): ?DynamicCommand
    {
        $command = $this->messageCommandRepository->getCommandMapping($commandName);

        if (is_null($command)) {
            return null;
        }

        //
        if ($command->instance instanceof DynamicCommand) {
            $this->executeDynamicCommand($command->instance, $command->method, $message);

            return $command->instance;
        }

        if (method_exists($command->instance, $command->method)) {
            $this->executeStaticCommand($command->instance, $command->method, $message);

            return null;
        } else {
            $className = $command->instance::class;
            $methodName = $command->method;
            throw new LogicException("The defined method '$methodName' of class '$className' for the command '$commandName' does not exist");
        }
    }

    private function executeStaticCommand($commandInstance, string $methodName, ChannelMessage $message): void
    {
        $commandInstance->$methodName($message);
        unset($commandInstance);
    }

    private function executeDynamicCommand(DynamicCommand $commandInstance, string $methodName, ChannelMessage $message): void
    {
        $commandInstance->$methodName($message);
    }

    public static function handleError(Throwable $e, ChannelMessage $message): void
    {
        $discord = env()->discord;

        $embed = (new Embed($discord))
            ->setTitle('Exception Caught')
            ->setDescription('An exception occurred while processing your request.')
            ->setColor('#FF0000') // Set color to red using hexadecimal value
            ->setFooter($discord->username)
            ->setTimestamp()
            ->addField(['name' => 'Type', 'value' => get_class($e)])
            ->addField(['name' => 'Message', 'value' => $e->getMessage()])
            ->addField(['name' => 'File', 'value' => $e->getFile()])
            ->addField(['name' => 'Line', 'value' => $e->getLine()]);

        $message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed));
    }
}
