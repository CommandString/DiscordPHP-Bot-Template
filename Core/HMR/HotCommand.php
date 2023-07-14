<?php

namespace Core\HMR;

use CommandString\Utils\GeneratorUtils;
use Core\Commands\Command;
use Core\Commands\CommandHandler;
use Core\Commands\CommandQueue;
use Core\Commands\QueuedCommand;
use Core\Disabled;
use Discord\Helpers\RegisteredCommand;
use Evenement\EventEmitter;
use Throwable;

use function Core\debug;
use function Core\discord;
use function Core\doesClassHaveAttribute;
use function Core\error;
use function Core\info;

class HotCommand extends EventEmitter
{
    public const EVENT_RELOAD = 'reload';

    protected ?HotCache $cachedScript = null;
    protected HotFile $file;

    public function __construct(
        public readonly string $name,
        protected RegisteredCommand $command,
        public readonly string $filePath
    ) {
        $this->file = new HotFile($filePath);

        $this->file->on(HotFile::EVENT_CHANGED, function () {
            debug('Hot Command Changed: ' . $this->name);
            $this->reload();
        });
    }

    protected function createCachedScript(): bool
    {
        $className = GeneratorUtils::uuid(8, range('a', 'z'));
        $temp = BOT_ROOT . '/Core/HMR/Cached/' . $className . '.php';
        $contents = preg_replace('/class\s+([a-zA-Z0-9_]+)/', 'class ' . $className, $this->file->getContents());
        $contents = preg_replace('/namespace\s+([a-zA-Z0-9_\\\\]+)/', 'namespace Core\\HMR\\Cached', $contents);

        $this->cachedScript?->deleteCachedFile();
        file_put_contents($temp, $contents);
        $this->cachedScript = new HotCache($className, $temp);
        try {
            require $temp;

            return true;
        } catch (Throwable $e) {
            discord()->getLogger()->error($e);

            return false;
        }
    }

    public function reload(): void
    {
        if ($this->createCachedScript() === false) {
            debug('Failed to create cached script: ' . $this->name);

            return;
        }

        debug('Created Cached Script: ' . $this->name);

        /** @var CommandHandler $command */
        $command = $this->cachedScript->createInstance();
        $attribute = doesClassHaveAttribute($command::class, Command::class);

        if (
            $command === null ||
            $attribute === false ||
            doesClassHaveAttribute($command::class, Disabled::class) !== false
        ) {
            debug('Command is not valid: ' . $this->name);

            return;
        }

        $this->command->setCallback($command->handle(...));
        $this->command->setAutoCompleteCallback($command->autocomplete(...));
        debug('Command Reloaded: ' . $this->name);

        (new CommandQueue())->appendCommand(new QueuedCommand(
            $attribute->newInstance(),
            $command
        ))->runQueue()->then(static function () {
            info('Reran Command Queue');
        }, static function (Throwable $e) {
            error('Failed to run command queue: ' . $e);
        });
    }
}
