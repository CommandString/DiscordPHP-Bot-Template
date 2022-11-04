<?php

namespace Discord\Bot\Commands;

use Discord\Bot\Config;
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
     * @param Interaction $interaction
     * 
     * @return void
     */
    abstract public function autocomplete(Interaction $interaction): void;

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
        $discord = Config::get()->discord;

        $function = function ($commands) use ($discord) {
            $command = $commands->get("name", $this->name);

            if (is_null($command)) {
                throw new Exception("Command $this->name isn't registered to the discord bot!");
            }

            if (!$this->isGuildCommand()) {
                $discord->application->commands->delete($command);
            } else {
                $discord->guilds->get("id", $this->guild)->commands->delete($command);
            }
        };

        if ($this->isGuildCommand()) {
            $discord->guilds->get("id", $this->guild)->commands->freshen()->done($function);
        } else {        
            $discord->application->commands->freshen()->done($function);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function save(): self
    {
        $command = new Command(Config::get()->discord, $this->config);

        /**
         * @var \Discord\Discord
         */
        $discord = Config::get()->discord;

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
        $discord = Config::get()->discord;
        
        $discord->listenCommand($this->name, function (Interaction $interaction) {
            $this->handler($interaction);
        }, function (Interaction $interaction) {
            $this->autocomplete($interaction);
        });
    }
}
