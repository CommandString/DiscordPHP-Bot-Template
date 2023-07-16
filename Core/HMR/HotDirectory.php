<?php

namespace Core\HMR;

use CommandString\Utils\FileSystemUtils;
use Evenement\EventEmitter;
use LogicException;
use React\EventLoop\Loop;

class HotDirectory extends EventEmitter
{
    public const EVENT_FILE_CHANGED = 'fileChanged';
    public const EVENT_FILE_ADDED = 'fileAdded';
    public const EVENT_FILE_REMOVED = 'fileRemoved';

    /** @var HotFile[] */
    protected array $files = [];

    public function __construct(
        public readonly string $directory,
        int $interval = 1
    ) {
        if (!file_exists($directory)) {
            throw new LogicException("Directory {$directory} does not exist");
        }

        $files = FileSystemUtils::getAllFiles($directory);

        foreach ($files as $file) {
            $hotFile = new HotFile($file, $interval);
            $this->files[$file] = $hotFile;

            $hotFile
                ->on(HotFile::EVENT_CHANGED, fn (HotFile $file) => $this->emit(self::EVENT_FILE_CHANGED, [$this, $file]))
                ->on(HotFile::EVENT_REMOVED, fn (HotFile $file) => $this->emit(self::EVENT_FILE_REMOVED, [$this, $file]));
        }

        Loop::addPeriodicTimer($interval, function () use ($interval) {
            foreach (FileSystemUtils::getAllFiles($this->directory) as $file) {
                if (isset($this->files[$file])) {
                    continue;
                }

                $this->files[$file] = new HotFile($file, $interval);

                $this->emit(self::EVENT_FILE_ADDED, [$this, $file]);
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
