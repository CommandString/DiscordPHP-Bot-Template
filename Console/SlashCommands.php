<?php

namespace Console;

use Common\Env;
use Discord\Discord;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function React\Async\await;

#[AsCommand(name: 'SlashCommands', description: 'Setup slash commands', aliases: ["sc"])]
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

        $verb = ($action !== "deleteall") ? $verbs[$action] : null;

        $commands = $input->getArgument("slash-commands");

        if ($action !== "deleteall" && empty($commands)) {
            $output->writeln("<error>You must supply slash-commands for this option</error>");
            die();
        }

        $env = Env::createFromEnv(__DIR__ . "/../.env");

        if (file_exists("SlashCommands.log")) {
            unlink("SlashCommands.log");
        }

        $env->discord = new Discord([
            'token' => $env->token,
            'logger' => (new Logger('Logger'))->pushHandler(new NullHandler())
        ]);

        $env->discord->on("ready", static function () use ($action, $output, $commands, $verb)
		{
            /**
             * @var Discord $discord
             */
            $discord = Env::get()->discord;

            if ($action === 'deleteall') {
                $globalCommands = await($discord->application->commands->freshen());

                foreach ($globalCommands as $command) {
                    $output->writeln("<info>Deleting global command $command->name</info>");
                    await($discord->application->commands->delete($command));
                }

                foreach ($discord->guilds as $guild) {
                    $guildCommands = await($guild->commands->freshen());

                    foreach ($guildCommands as $command) {
                        $output->writeln("<info>Deleting command $command->name from guild $guild->name:$guild->id</info>");
                        await($guild->commands->delete($command));
                    }
                }

                $discord->close();
            } else {
				foreach ($commands as $i => $value) {
					$command = "Commands\\{$value}";

					if (!class_exists($command)) {
						$output->writeln('<error>Command class "' . $command . '" cannot be found!</error>');
						die();
					}

					$commandName = $command::getName();

					if (is_array($commandName)) {
						$commandName = $commandName[0];
					}

					$output->writeln("<info>{$verb} {$commandName} command</info>");
					$close = ($i === count($commands)-1);

					$command::$action()->done(static function ($commandName) use ($output, $close, $discord)
					{
						$output->writeln("<info>Successfully saved {$commandName}</info>");

						if ($close) {
							$discord->close();
						}
					}, static function (Throwable $passed) use ($output, $action, $commandName, $close, $discord)
					{
							$output->writeln("<error>Failed to {$action} {$commandName}</error>\n</info>{$passed->getMessage()}</info>");

							if ($close) {
								$discord->close();
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
