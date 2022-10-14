<?php

namespace cmdstr\Discord\Commands;

use cmdstr\Discord\Config;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Interaction;
use Exception;

abstract class Template {
    /**
     * @param string $name
     * 
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        switch ($name) {
            case "name":
                return $this->getName();
            case "config":
                $config = $this->getConfig();

                if (is_array($config)) {
                    return $config;
                }

                return $config->toArray();
            case "guild":
                return $this->getGuild();
        }

        return $this->$name;
    }

    /**
     * @param Interaction $interaction
     * 
     * @return void
     */
    abstract public function handler(Interaction $interaction): void;

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return CommandBuilder
     */
    abstract public function getConfig(): CommandBuilder|array;

    /**
     * @return string
     */
    abstract public function getGuild(): string;

    /** 
     * @return bool
     */
    private function isGuildCommand(): bool
    {
        return (strlen($this->guild) > 0);
    }

    /**
     * @return self
     */
    public function delete(): self
    {
        /**
         * @var \Discord\Discord
         */
        $discord = Config::getInstance()->discord;
        
        $discord->application->commands->freshen()->done(function ($results) use ($discord) {
            $command = $results->get("name", $this->name);

            if (is_null($command)) {
                throw new Exception("Command $this->name isn't registered to the discord bot!");
            }

            $discord->application->commands->delete($command);
        });

        return $this;
    }

    /**
     * @return self
     */
    public function save(): self
    {
        $command = new Command(Config::getInstance()->discord, $this->config);

        /**
         * @var \Discord\Discord
         */
        $discord = Config::getInstance()->discord;

        if ($this->isGuildCommand()) {
            $discord->guilds[$this->guild]->commands->save($command);
        } else {
            $discord->application->commands->save($command);
        }

        return $this;
    }

    /**
     * @return void
     */
    public function listen(): void
    {
        /**
         * @var \Discord\Discord
         */
        $discord = Config::getInstance()->discord;
        
        $discord->listenCommand($this->name, function (Interaction $interaction) {
            $this->handler($interaction);
        });
    }
}
