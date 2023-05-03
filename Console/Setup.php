<?php

namespace Console;

use stdClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'setup', description: 'First time setup', aliases: ["s"])]
class Setup extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists('./.env.example')) {
            $output->writeln('<error>Cannot find env.example.json...exiting</error>');
            return Command::FAILURE;
        }

        $exampleEnv = file_get_contents('./.env.example', true);
        $lines = explode("\n", $exampleEnv);

        $template = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode('=', $line);
            $key = $parts[0];
            $value = $parts[1];

            $template[$key] = $value;
        }

        $helper = $this->getHelper('question');

        $getConfig = static function ($template, $prefix = '') use (&$getConfig, $helper, $input, $output)
		{
            $items = [];

            foreach ($template as $name => $item) {
                if (is_array($item) || $item instanceof stdClass) {
                    $items[$name] = $getConfig($item, "{$name}->");
                    continue;
                }

                $questionString = "<question>".ucfirst($prefix).ucfirst($name);


                if ($item !== '') {
                    if (is_bool($item)) {
                        $itemString = ($item) ? 'true' : 'false';
                    } else {
                        $itemString = $item;
                    }

                    $questionString .= " (default: {$itemString})";
                }

                $questionString .= ': ';

                $question = new Question($questionString, $item);
                $items[$name] = $helper->ask($input, $output, $question);
            }

            return $items;
        };

        $config = $getConfig($template);
        $env = '';

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $env .= "{$key}_{$subKey}={$subValue}\n";
                }
            } else {
                $env .= "{$key}={$value}\n";
            }
        }

        file_put_contents(__DIR__ . '/../.env', $env);

        $output->writeln('<info>Successfully setup environment</info>');

        return Command::SUCCESS;
    }
}
