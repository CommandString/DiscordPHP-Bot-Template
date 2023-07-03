<?php

namespace Core\HMR;

use CommandString\Utils\GeneratorUtils;
use Core\Commands\CommandHandler;
use Discord\Helpers\RegisteredCommand;
use Evenement\EventEmitter;

class HotCommand extends EventEmitter
{
    public const EVENT_RELOAD = 'reload';

    protected ?HotCache $cachedScript = null;

    protected HotFile $file;

    public function __construct(
        public readonly string $name,
        protected readonly RegisteredCommand $command,
        public readonly string $filePath
    ) {
        $this->file = new HotFile($filePath);

        $this->file->on(HotFile::EVENT_HAS_CHANGED, function () {
            $this->reload();
        });
    }

    protected function createCachedScript(): void
    {
        $className = GeneratorUtils::uuid(8, range('a', 'z'));
        $temp = BOT_ROOT . '/Core/HMR/' . $className . '.php';
        $contents = preg_replace('/class\s+([a-zA-Z0-9_]+)/', 'class ' . $className, $this->file->getContents());
        $contents = preg_replace('/namespace\s+([a-zA-Z0-9_\\\\]+)/', 'namespace Core\\HMR\\Cached', $contents);

        $this->cachedScript?->deleteCachedFile();
        file_put_contents($temp, $contents);
        $this->cachedScript = new HotCache($className, $temp);
        require $temp;
    }

    public function reload(): void
    {
        $this->createCachedScript();

        /** @var CommandHandler $command */
        $command = $this->cachedScript->createInstance();

        $this->command->setCallback($command->handle(...));
        $this->command->setAutoCompleteCallback($command->autocomplete(...));
        $this->emit(self::EVENT_RELOAD, [$this]);
    }
}