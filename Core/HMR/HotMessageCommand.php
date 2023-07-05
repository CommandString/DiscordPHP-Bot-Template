<?php

namespace Core\HMR;

use CommandString\Utils\GeneratorUtils;
use Throwable;

use function Core\debug;
use function Core\discord;

class HotMessageCommand
{
    protected ?HotCache $cachedScript = null;

    protected HotFile $file;

    public function __construct(
        public readonly string $name,
        public readonly string $filePath
    ) {
        $this->file = new HotFile($filePath);

        $this->file->on(HotFile::EVENT_CHANGED, function () {
            $this->reload();
        });

        $this->createCachedScript();
    }

    protected function createCachedScript(): bool
    {
        $className = GeneratorUtils::uuid(8, range('a', 'z'));
        $temp = BOT_ROOT . '/Core/HMR/' . $className . '.php';
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

    public function getInstance()
    {

        return $this->cachedScript->createInstance();
    }

    public function reload(): void
    {
        if ($this->createCachedScript() === false) {
            debug('Failed to create cached script: ' . $this->name);

            return;
        }

        debug('Created Cached Script: ' . $this->name);


        // $this->emit(self::EVENT_RELOAD, [$this]);
    }
}
