<?php

namespace Core\HMR;

use CommandString\Utils\GeneratorUtils;

class HotMessageCommand
{
    protected ?HotCache $cachedScript = null;

    protected HotFile $file;

    public function __construct(
        public readonly string $filePath
    ) {
        $this->file = new HotFile($filePath);

        $this->file->on(HotFile::EVENT_HAS_CHANGED, function () {
            $this->reload();
        });

        $this->createCachedScript();
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

    public function getInstance()
    {

        return $this->cachedScript->createInstance();
    }

    public function reload(): void
    {
        $this->createCachedScript();

        // $this->emit(self::EVENT_RELOAD, [$this]);
    }
}
