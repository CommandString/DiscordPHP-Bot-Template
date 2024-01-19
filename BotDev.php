<?php

/** @noinspection FileClassnameCaseInspection */

use Core\HMR\HotDirectory;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

use function React\Async\await;

require_once __DIR__ . '/vendor/autoload.php';

class Command
{
    protected const TEMP = __DIR__ . '/temp';

    private array $process = [];
    private int $stdoutPos = 0;
    private int $stderrPos = 0;

    public function __construct(
        public readonly string $command,
        public readonly array $args = [],
    ) {
    }

    public function create(string $command, array $args = []): self
    {
        return new self($command, $args);
    }

    private function procExecute(string $command): array
    {
        $stdout = tempnam(sys_get_temp_dir(), 'dphp');
        $stderr = tempnam(sys_get_temp_dir(), 'dphp');
        $process = proc_open(
            $command,
            [
                1 => ['file', $stdout, 'w'],
                2 => ['file', $stderr, 'w'],
            ],
            $pipes
        );

        return [
            'files' => [$stdout, $stderr],
            'command' => $command,
            'process' => &$process,
        ];
    }

    public function execute(): PromiseInterface
    {
        if ($this->isRunning()) {
            throw new LogicException('Command is already running');
        }

        return new Promise(function ($resolve, $reject) {
            $this->process = $this->procExecute($this->command . ' ' . implode(' ', $this->args));
            Loop::addPeriodicTimer(
                1,
                function (TimerInterface $timer) use (&$stdout, &$stderr, $reject, $resolve) {
                    $status = proc_get_status($this->process['process']);
                    $stdout = file_get_contents($this->process['files'][0]);
                    $stderr = file_get_contents($this->process['files'][1]);

                    if ($status['running']) {
                        if ($this->stdoutPos < strlen($stdout)) {
                            echo substr($stdout, $this->stdoutPos);
                            $this->stdoutPos = strlen($stdout);
                        }

                        if ($this->stderrPos < strlen($stderr)) {
                            echo substr($stderr, $this->stderrPos);
                            $this->stderrPos = strlen($stderr);
                        }

                        return;
                    }

                    if ($status['exitcode'] !== 0) {
                        $reject([$stderr, $this->command, $this->args]);
                    } else {
                        $resolve($stdout, $stderr);
                    }

                    $this->process = [];

                    Loop::cancelTimer($timer);
                }
            );
        });
    }

    public function isRunning(): bool
    {
        return $this->process !== [];
    }

    public function getProcess(): array
    {
        return $this->process;
    }

    public function kill(): void
    {
        if (!$this->isRunning()) {
            return;
        }

        $this->stdoutPos = $this->stderrPos = 0;

        proc_terminate($this->process['process']);
    }
}

$directory = new HotDirectory(__DIR__);

$restart = static function () use (&$command) {
    $command ??= new Command('php', ['Bot.php']);
    $time = date('H:i:s');
    echo "\nRestarting bot ({$time})...\n";

    $command->kill();

    await(new Promise(static function ($resolve, $reject) use (&$command) {
        Loop::addPeriodicTimer(2, static function (TimerInterface $timer) use (&$command, $resolve) {
            if ($command->isRunning()) {
                echo "Command is still running\n";

                return;
            }
            try {
                Loop::cancelTimer($timer);
            } catch (Throwable $e) {
            }

            $resolve();
        });
    }));

    try {
        $command->execute();
    } catch (Throwable $e) {
        echo "Error: {$e->getMessage()}\n";
    }
};

$restart();

$directory->on(HotDirectory::EVENT_FILE_ADDED, $restart(...));
$directory->on(HotDirectory::EVENT_FILE_CHANGED, $restart(...));
$directory->on(HotDirectory::EVENT_FILE_REMOVED, $restart(...));

try {
    Loop::run();
} catch (Throwable $e) {
    Loop::run();
}
