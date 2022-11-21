<?php

namespace Discord\Bot\Commands\Examples;

use Discord\Bot\Commands\Template;
use Discord\Bot\Env;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Helpers\Collection;
use Discord\Parts\Interactions\Command\Choice;
use Discord\Parts\Interactions\Command\Option;
use Discord\Parts\Interactions\Interaction;
use Exception;

// This is used for testing the new getName() method update and a decent example of a more complex command for beginners

/**
 * @inheritDoc CommandTemplate
 */
class Randomize extends Template {
    public function handler(Interaction $interaction): void
    {
        $subCommand = $this->getCurrentSubcommand($interaction);
        $options = $subCommand->options;
        $text = $options->get("name", "text")->value;
        $direction = $options->name !== "backward";
        $randomizedText = $this->randomize($text, $direction);

        $interaction->respondWithMessage(MessageBuilder::new()->setContent("`$text` -> `$randomizedText`"), true);
    }

    public function autocomplete(Interaction $interaction): void
    {
        $newChoice = function (string $text): Choice
        {
            $text = str_split($text, 100)[0];

            return (new Choice(Env::get()->discord))
                ->setName($text)
                ->setValue($text)
            ;
        };

        $subCommand = $this->getCurrentSubcommand($interaction);
        $options = $subCommand->options;
        $text = $options->get("name", "text")->value;
        $direction = $subCommand->name !== "backward";
        $choices = [];

        for ($i = 0; $i <= 24; $i++) {
            $randomizedText = $this->randomize($text, $direction);
            $choices[] = $newChoice($randomizedText);
        }

        $interaction->autoCompleteResult($choices);
    }

    /**
     * @param boolean $direction true for forward false for backward
     * @return string
     */
    public function randomize(string $text, bool $direction = true): string
    {
        $letters = str_split($text);
        $randomizedText = "";
        $used = [];

        for ($i = 0; $i < count($letters); $i++) {
            $key = rand(0, count($letters)-1);

            if (in_array($key, $used)) { // prevents a letter from being inserted twice
                $i--;
                continue;
            }

            $used[] = $key;
            $randomizedText .= $letters[$key];
        }

        if (!$direction) {
            $randomizedLetters = str_split($randomizedText);
            $randomizedText = "";
            for ($i = count($randomizedLetters)-1; $i >= 0; $i--) {
                $randomizedText .= $randomizedLetters[$i];
            }
        }
        
        return $randomizedText;
    }

    public function getCurrentSubcommand(Interaction $interaction)
    {
        $options = $interaction->data->options;

        $options = $options->get("name", "direction")?->options?->get("name", "forward") ?? $options->get("name", "direction")?->options?->get("name", "backward") ?? $options->get("name", "up");

        if ($options === null) {
            throw new Exception("Was unable to find options!?!?");
        }

        return $options;
    }

    public function getName(): array
    {
        return ["randomize", ["direction", "forward"], ["direction", "backward"]];
    }

    public function getConfig(): CommandBuilder|array
    {
        $textOption = (new Option(Env::get()->discord))
            ->setName("text")
            ->setDescription("Text to randomize")
            ->setType(Option::STRING)
            ->setAutoComplete(true)
            ->setRequired(true)
        ;

        return (new CommandBuilder)
            ->setName($this->getName()[0])
            ->setDescription("Randomize text")
            ->addOption((new Option(Env::get()->discord))
                ->setName("direction")
                ->setDescription("The subcommand group")
                ->setType(Option::SUB_COMMAND_GROUP)
                ->addOption((new Option(Env::get()->discord))
                    ->setName("forward")
                    ->setDescription("Randomize Forward")
                    ->setType(Option::SUB_COMMAND)
                    ->addOption($textOption)
                )
                ->addOption((new Option(Env::get()->discord))
                    ->setName("backward")
                    ->setDescription("Randomize Backwards")
                    ->setType(Option::SUB_COMMAND)
                    ->addOption($textOption)
                )
            )
            ->addOption((new Option(Env::get()->discord))
                ->setName("up")
                ->setDescription("randomize up")
                ->setType(Option::SUB_COMMAND)
                ->addOption($textOption)
            )
        ;
    }

    public function getGuild(): string
    {
        return "";
    }
}
