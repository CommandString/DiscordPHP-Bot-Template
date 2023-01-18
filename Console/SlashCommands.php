<?php

namespace Console;


use CommandString\Env\Env;
use Discord\Discord;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use \Symfony\Component\Console\Attribute\AsCommand;
use \Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Throwable;

#[AsCommand(name: 'SlashCommands', description: 'Setup slash commands')]
class SlashCommands extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'The action (save, delete, deleteall)')
            ->addArgument('slash-commands', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The slash-command(s) to perform said action on')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $verbs = ["save" => "Saving", "delete" => "Deleting"];

        $action = $input->getArgument('action');

        if (!in_array($action, ["save", "delete", "deleteall"])) {
            $output->writeln("<error>Invalid action</error>");
            die();
        }
        
        $verb = $verbs[$action];

        $commands = $input->getArgument("slash-commands");
        
        if ($action !== "deleteall" && empty($commands)) {
            $output->writeln("<error>You must supply slash-commands for this option</error>");
            die();
        }

        $env = Env::createFromJsonFile('./env.json');

        if (file_exists("SlashCommands.log")) {
            unlink("SlashCommands.log");
        }

        $env->discord = new Discord([
            'token' => $env->token,
            'logger' => (new Logger('Logger'))->pushHandler(new NullHandler())
        ]);

        $env->discord->on("ready", function () use ($commands, $action, $output, $verb) {
            /**
             * @var \Discord\Discord
             */
            $discord = Env::get()->discord;
            
            if ($action === 'deleteall') {
                $discord->application->commands->freshen()->done(function ($results) use ($discord, $output) {
                    foreach ($results as $command) {
                        $output->writeln("<info>Deleting global command $command->name</info>");
                        $discord->application->commands->delete($command);
                    }
                });
        
                foreach ($discord->guilds as $guild) {
                    $guild->commands->freshen()->done(function ($results) use ($guild, $output) {
                        foreach ($results as $command) {
                            $output->writeln("<info>Deleting command $command->name from guild $guild->name:$guild->id</info>");
                            $guild->commands->delete($command);
                        }
                    });
                }

                $discord->close(true);
            } else {
                for ($i = 0; $i < count($commands); $i++) {
                    $command = "Commands\\{$commands[$i]}";

                    if (!class_exists($command)) {
                        $output->writeln('<error>Command class "' . $command . '" cannot be found!</error>');
                        die();
                    }

                    $commandName = $command::getName();

                    if (is_array($commandName)) {
                        $commandName = $commandName[0];
                    }

                    $output->writeln("<info>$verb $commandName command</info>");
                    $close = ($i === count($commands)-1);

                    $command::$action()->done(function ($commandName) use ($output, $discord, $close) {
                        $output->writeln("<info>Successfully saved $commandName</info>");

                        if ($close) {
                            $discord->close(true);
                        }
                    }, function (Throwable $passed) use ($output, $action, $commandName, $discord, $close) {
                            $output->writeln("<error>Failed to $action $commandName</error>\n</info>{$passed->getMessage()}</info>");

                            if ($close) {
                                $discord->close(true);
                            }
                        }
                    );
                }
            }
        });
        
        $env->discord->run();

        return Command::SUCCESS;
    }
}