<?php

namespace Discord\Bot\Commands;

use Discord\Bot\Env;
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
    abstract public function getName(): string|array;

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
        $discord = Env::get()->discord;

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
        $command = new Command(Env::get()->discord, $this->config);

        /**
         * @var \Discord\Discord
         */
        $discord = Env::get()->discord;

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
        $discord = Env::get()->discord;
        
        $listen = function (string|array $name) use ($discord) {
            try {
                $discord->listenCommand($name, function (Interaction $interaction) {
                    $this->handler($interaction);
                }, function (Interaction $interaction) {
                    $this->autocomplete($interaction);
                });
            } catch (\LogicException $e) {
                echo "Warning caught: {$e->getMessage()}\nIf this is about a command already existing for a command you're listening for that has a separate subcommand handler you can safely ignore this :)";
            }
        };

        if (!is_array($this->name)) {
            $listen($this->name);
        } else {
            foreach ($this->name as $name) {
                if (!is_array($name)) {
                    $listen($name);
                } else {
                    $names = $name;
                    $listen([$this->name[0], ...$names]);
                }
            }
        }
    }
}
