<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Filesystem\Util\Archive;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Unzip
{
    use PathHelper;

    protected const URI = 'uri';
    protected const DIRECTORY_SEPARATOR = DIRECTORY_SEPARATOR;
    public const DS_UNIX = "/";
    public const DS_WIN = "\\";
    public const BASE_DIR = '.';
    protected \ZipArchive $zip;
    protected bool $error_reading_zip = false;
    protected string $path_to_zip;
    private int $amount_of_entries = 0;

    public function __construct(
        protected UnzipOptions $options,
        protected FileStream $stream
    ) {
        $this->path_to_zip = $this->stream->getMetadata(self::URI);
        $this->zip = new \ZipArchive();
        try {
            $this->zip->open($this->path_to_zip);
            $this->amount_of_entries = $this->zip->count();
        } catch (\Throwable) {
            $this->error_reading_zip = true;
        }
    }


    /**
     * @return \Generator<bool|string>
     */
    public function getPaths(): \Generator
    {
        if (!$this->error_reading_zip) {
            for ($i = 0, $i_max = $this->amount_of_entries; $i < $i_max; $i++) {
                $path = $this->zip->getNameIndex($i, \ZipArchive::FL_UNCHANGED);
                if ($this->isPathIgnored($path, $this->options)) {
                    continue;
                }
                yield $path;
            }
        }
    }

    /**
     * @return \Generator|FileStream[]
     */
    public function getStreams(): \Generator
    {
        foreach ($this->getPaths() as $path) {
            yield Streams::ofResource($this->zip->getStream($path));
        }
    }

    public function getAmountOfDirectories(): int
    {
        return iterator_count($this->getDirectories());
    }

    /**
     * Yields the directory-paths of the currently open zip-archive.
     * This fixes the issue that win and mac zip archives have different directory structures.
     * @return \Generator|string[]
     */
    public function getDirectories(): \Generator
    {
        $directories = [];
        foreach ($this->getPaths() as $path) {
            if (substr($path, -1) === self::DS_UNIX || substr($path, -1) === self::DS_WIN) {
                $directories[] = $path;
            }
        }

        $directories_with_parents = [];

        foreach ($directories as $directory) {
            $parent = dirname($directory) . self::DIRECTORY_SEPARATOR;
            if ($parent !== self::BASE_DIR . self::DIRECTORY_SEPARATOR && !in_array($parent, $directories)) {
                $directories_with_parents[] = $parent;
            }
            $directories_with_parents[] = $directory;
        }

        $directories_with_parents = array_unique($directories_with_parents);
        sort($directories_with_parents);
        yield from $directories_with_parents;
    }

    public function getAmountOfFiles(): int
    {
        return iterator_count($this->getFiles());
    }

    /**
     * Yields the file-paths of the currently open zip-archive.
     * @return \Generator|string[]
     */
    public function getFiles(): \Generator
    {
        $files = [];
        foreach ($this->getPaths() as $path) {
            if (substr($path, -1) !== self::DS_UNIX && substr($path, -1) !== self::DS_WIN) {
                $files[] = $path;
            }
        }
        sort($files);
        yield from $files;
    }

    public function hasMultipleRootEntriesInZip(): bool
    {
        $amount = 0;
        foreach ($this->getDirectories() as $zip_directory) {
            $dirname = dirname($zip_directory);
            if ($dirname === self::BASE_DIR) {
                $amount++;
            }
            if ($amount > 1) {
                return true;
            }
        }
        foreach ($this->getFiles() as $zip_file) {
            $dirname = dirname($zip_file);
            if ($dirname === self::BASE_DIR) {
                $amount++;
            }
            if ($amount > 1) {
                return true;
            }
        }
        return false;
    }


    public function extract(): bool
    {
        if ($this->error_reading_zip) {
            return false;
        }

        $destination_path = $this->options->getZipOutputPath();
        if ($destination_path === null) {
            return false;
        }

        if ($this->options->isFlat()) {
            foreach ($this->getStreams() as $stream) {
                $uri = $stream->getMetadata(self::URI);
                if (substr($uri, -1) === self::DIRECTORY_SEPARATOR) {
                    continue; // Skip directories
                }
                file_put_contents(
                    $destination_path . self::DIRECTORY_SEPARATOR . basename($uri),
                    $stream->getContents()
                );
            }
        } else {
            $this->zip->extractTo($destination_path, iterator_to_array($this->getPaths()));
        }
        return true;
    }

    public function hasZipReadingError(): bool
    {
        return $this->error_reading_zip;
    }
}
