<?php

namespace Console;

use stdClass;
use \Symfony\Component\Console\Attribute\AsCommand;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'setup', description: 'First time setup', aliases: ["s"])]
class Setup extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists('./env.example.json')) {
            $output->writeln('<error>Cannot find env.example.json...exiting</error>');
            return Command::FAILURE;
        }

        $template = json_decode(file_get_contents('./env.example.json', true));

        $helper = $this->getHelper('question');

        $getConfig = function ($template, $prefix = '') use (&$getConfig, $input, $output, $helper) {
            $items = [];

            foreach ($template as $name => $item) {
                if (is_array($item) || $item instanceof stdClass) {
                    $items[$name] = $getConfig($item, "$name->");
                    continue;
                }

                $questionString = "<question>".ucfirst($prefix).ucfirst($name);
                
                
                if ($item !== '') {
                    if (is_bool($item)) {
                        $itemString = ($item) ? 'true' : 'false';
                    } else {
                        $itemString = $item;
                    }
                    
                    $questionString .= " (default: $itemString)";
                }
                
                $questionString .= ': ';

                $question = new Question($questionString, $item);
                $items[$name] = $helper->ask($input, $output, $question);
            }

            return $items;
        };

        $config = $getConfig($template);

        file_put_contents(__DIR__ . '/../env.json', json_encode($config, JSON_PRETTY_PRINT));

        $output->writeln('<info>Successfully setup environment</info>');

        return Command::SUCCESS;
    }
}