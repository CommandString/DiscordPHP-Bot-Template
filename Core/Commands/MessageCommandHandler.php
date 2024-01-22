<?php

namespace Core\Commands;

use Core\HMR\InstanceManager;

/**
 * Class MessageCommandHandler
 *
 * Designed to manage and handle various commands, providing a structured way to define and handle different commands by encapsulating information
 * such as command name, command class, subcommands, and default methods.
 */
class MessageCommandHandler
{
    /**
     * @var string The name of the command.
     */
    protected string $commandName;

    /**
     * @var string The class name associated with the command.
     */
    protected string $commandClass;

    /**
     * @var string The default method name for the command.
     */
    protected string $defaultMethod;

    /**
     * @var array Associative array of subcommands and their associated method names.
     */
    protected array $subCommands = [];

    /**
     * @var InstanceManager|null An optional instance manager for creating instances of the command class.
     */
    protected ?InstanceManager $instanceManager = null;

    /**
     * Static factory method to create a new instance of MessageCommandHandler.
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Set the command name.
     *
     * @return $this
     */
    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    /**
     * Add a subcommand with its associated method name.
     *
     * @return $this
     */
    public function addSubCommand(string $subCommandName, string $methodName): self
    {
        $this->subCommands[$subCommandName] = $methodName;

        return $this;
    }

    /**
     * Set the default method name, as default it'll use handle().
     *
     * @return $this
     */
    public function setDefaultMethod(string $methodName = 'handle'): self
    {
        $this->defaultMethod = $methodName;

        return $this;
    }

    /**
     * Set the command class name.
     *
     * @return $this
     */
    public function setCommandClass(string $className): self
    {
        $this->commandClass = $className;

        return $this;
    }

    /**
     * Set the instance manager for creating instances of the command class.
     *
     * @return $this
     */
    public function setInstanceManager(InstanceManager $instanceManager): self
    {
        $this->instanceManager = $instanceManager;
        $this->instanceManager->set($this->commandName, $this->commandClass);

        return $this;
    }

    /**
     * Validate the required properties of the MessageCommandHandler.
     *
     * @throws \LogicException If any of the required properties are empty.
     */
    public function validate(): void
    {
        if (empty($this->commandName)) {
            throw new \LogicException('Command name is required.');
        }

        if (empty($this->commandClass)) {
            throw new \LogicException('Command class is required.');
        }

        if (empty($this->defaultMethod)) {
            throw new \LogicException('Default Method of Command Class is required!');
        }
    }

    /**
     * Get the command name.
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }

    /**
     * Get the command class name.
     */
    public function getCommandClass(): string
    {
        return $this->commandClass;
    }

    /**
     * Get the subcommands and their associated method names.
     */
    public function getSubCommands(): array
    {
        return $this->subCommands;
    }

    /**
     * Get the method name associated with a specific subcommand.
     */
    public function getSubCommand(string $subCommandName): ?string
    {
        return $this->subCommands[$subCommandName] ?? null;
    }

    /**
     * Get the default method name.
     */
    public function getDefaultMethod(): string
    {
        return $this->defaultMethod;
    }

    /**
     * Create an instance of the command class.
     *
     * @return mixed
     */
    public function createInstance()
    {
        if (is_null($this->instanceManager)) {
            return new $this->commandClass();
        }

        return $this->instanceManager->createInstance();
    }
}
