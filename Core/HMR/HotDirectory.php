<?php

namespace Core\HMR;

use CommandString\Utils\FileSystemUtils;
use Evenement\EventEmitter;
use React\EventLoop\Loop;

use function Core\discord;

class HotDirectory extends EventEmitter
{
    public const EVENT_FILE_CHANGED = 'fileChanged';
    public const EVENT_FILE_ADDED = 'fileAdded';
    public const EVENT_FILE_REMOVED = 'fileRemoved';

    /** @var HotFile[] */
    protected array $files = [];

    public function __construct(
        public readonly string $directory
    ) {
        if (!file_exists($directory)) {
            discord()->getLogger()->error("Directory {$directory} does not exist");

            return;
        }

        $files = FileSystemUtils::getAllFiles($directory);

        foreach ($files as $file) {
            $this->files[$file] = new HotFile($file);

            $file->on(HotFile::EVENT_CHANGED, function (HotFile $file) {
                $this->emit(self::EVENT_FILE_CHANGED, [$file]);
            });

            $file->on(HotFile::EVENT_REMOVED, function (HotFile $file) {
                $this->emit(self::EVENT_FILE_REMOVED, [$file]);
            });
        }

        Loop::addPeriodicTimer(1, function () {
            foreach (FileSystemUtils::getAllFiles($this->directory) as $file) {
                if (isset($this->files[$file])) {
                    continue;
                }

                $this->files[$file] = new HotFile($file);

                $this->emit(self::EVENT_FILE_ADDED, [$file]);
            }
        });
    }

    /**
     * @return HotFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
