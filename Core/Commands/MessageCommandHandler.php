<?php

namespace Core\Commands;

use Core\HMR\InstanceHandler;

class MessageCommandHandler
{
    protected string $commandName;
    protected string $commandClass;
    protected string $defaultMethod;
    protected array $subCommands = [];
    protected ?InstanceHandler $instanceManager = null;

    /**
     * Static factory method to create a new instance of MessageCommand.
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Set the command name.
     */
    public function setCommandName(string $commandName): self
    {
        $this->commandName = $commandName;

        return $this;
    }

    /**
     * Add a subcommand with its associated method name.
     */
    public function addSubCommand(string $subCommandName, string $methodName): self
    {
        $this->subCommands[$subCommandName] = $methodName;

        return $this;
    }

    /**
     * Set the default method name, as default it'll use handle()
     */
    public function setDefaultMethod(string $methodName = 'handle'): self
    {
        $this->defaultMethod = $methodName;

        return $this;
    }

    /**
     * Set the command class name.
     */
    public function setCommandClass(string $className): self
    {
        $this->commandClass = $className;

        return $this;
    }

    /**
     * Set the instance manager for creating instances of the command class.
     */
    public function setInstanceManager(InstanceHandler $instanceManager): self
    {
        $this->instanceManager = $instanceManager;
        $this->instanceManager->set($this->commandName, $this->commandClass);

        return $this;
    }

    /**
     * Validate the required properties of the MessageCommand.
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
     * Create an instance of the command class
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
