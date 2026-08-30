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
use ILIAS\Filesystem\Util;

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
    private ?bool $within_limits = null;

    public function __construct(
        protected UnzipOptions $options,
        protected FileStream $stream
    ) {
        $this->path_to_zip = $this->stream->getMetadata(self::URI);
        $this->zip = new \ZipArchive();
        try {
            $this->zip->open($this->path_to_zip, \ZipArchive::RDONLY);
            $this->amount_of_entries = $this->zip->count();
        } catch (\Throwable) {
            $this->error_reading_zip = true;
        }
    }

    /**
     * @return \Closure
     */
    protected function pathToStreamGenerator(): \Closure
    {
        return function (\Generator $paths): \Generator {
            foreach ($paths as $path) {
                $resource = $this->zip->getStream($path);

                yield Streams::ofResource($resource);
            }
        };
    }

    /**
     * @return \Generator<bool|string>
     */
    public function getPaths(): \Generator
    {
        if (!$this->error_reading_zip && $this->isWithinLimits()) {
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
        $paths_to_stream_generator = $this->pathToStreamGenerator();

        if ($this->options->getDirectoryHandling() === ZipDirectoryHandling::FLAT_STRUCTURE) {
            yield from $paths_to_stream_generator($this->getFiles());
        } else {
            yield from $paths_to_stream_generator($this->getPaths());
        }
    }

    /**
     * @return \Generator|FileStream[]
     */
    public function getFileStreams(): \Generator
    {
        $paths_to_stream_generator = $this->pathToStreamGenerator();

        yield from $paths_to_stream_generator($this->getFiles());
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
                continue;
            }
            if ((str_contains($path, self::DS_UNIX) || str_contains($path, self::DS_WIN))) {
                $directories[] = dirname($path) . self::DIRECTORY_SEPARATOR;
            }
        }

        $directories_with_parents = [];

        foreach ($directories as $directory) {
            $parent = dirname($directory) . self::DIRECTORY_SEPARATOR;
            if ($parent !== self::BASE_DIR . self::DIRECTORY_SEPARATOR && !in_array($parent, $directories, true)) {
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

    /**
     * Guards against decompression bombs by checking the archive metadata (entry count, total
     * uncompressed size and the uncompressed/compressed ratio) before any data is handed out.
     * The sizes are read from the central directory via statIndex(); a crafted archive that lies
     * about these sizes fails during the subsequent extraction anyway.
     *
     * An archive that exceeds the limits behaves like an empty one: extract() returns false and
     * all generators (getPaths(), getFiles(), getStreams(), getFileStreams(), ...) yield nothing.
     * Callers that consume the streams themselves should ask this method first, so they can tell
     * a rejected archive from an empty one and report it to the user.
     */
    public function isWithinLimits(): bool
    {
        return $this->within_limits ??= $this->calculateIsWithinLimits();
    }

    private function calculateIsWithinLimits(): bool
    {
        $max_entries = $this->options->getMaxAmountOfEntries();
        if ($max_entries > UnzipOptions::UNLIMITED && $this->amount_of_entries > $max_entries) {
            return false;
        }

        $total_compressed = 0;
        $total_uncompressed = 0;
        for ($i = 0; $i < $this->amount_of_entries; $i++) {
            $stat = $this->zip->statIndex($i, \ZipArchive::FL_UNCHANGED);
            if ($stat === false) {
                continue;
            }
            $total_compressed += max(0, (int) ($stat['comp_size'] ?? 0));
            $total_uncompressed += max(0, (int) ($stat['size'] ?? 0));
        }

        $max_uncompressed = $this->options->getMaxUncompressedSize();
        if ($max_uncompressed > UnzipOptions::UNLIMITED && $total_uncompressed > $max_uncompressed) {
            return false;
        }

        $max_ratio = $this->options->getMaxCompressionRatio();
        if (
            $max_ratio > UnzipOptions::UNLIMITED
            && $total_compressed > 0
            && $total_uncompressed > $this->options->getRatioCheckMinUncompressedSize()
            && ($total_uncompressed / $total_compressed) > $max_ratio
        ) {
            return false;
        }

        return true;
    }

    public function extract(): bool
    {
        if ($this->error_reading_zip) {
            return false;
        }

        if (!$this->isWithinLimits()) {
            return false;
        }

        $destination_path = $this->options->getZipOutputPath();
        if ($destination_path === null) {
            return false;
        }

        switch ($this->options->getDirectoryHandling()) {
            case ZipDirectoryHandling::KEEP_STRUCTURE:
                break;
            case ZipDirectoryHandling::ENSURE_SINGLE_TOP_DIR:
                // top directory with same name as the ZIP without suffix
                $zip_path = $this->stream->getMetadata(self::URI);
                $sufix = '.' . pathinfo((string) $zip_path, PATHINFO_EXTENSION);
                $top_directory = basename((string) $zip_path, $sufix);

                // first we check if the ZIP contains the top directory
                $has_top_directory = true;
                foreach ($this->getPaths() as $path) {
                    $has_top_directory = str_starts_with($path, $top_directory) && $has_top_directory;
                }

                // if not, we prepend the top directory to the destination path
                if (!$has_top_directory) {
                    $destination_path .= self::DIRECTORY_SEPARATOR . $top_directory;
                }
                break;
            case ZipDirectoryHandling::FLAT_STRUCTURE:
                if (!is_dir($destination_path) && (!mkdir($destination_path, 0777, true) && !is_dir($destination_path))) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $destination_path));
                }

                foreach ($this->getStreams() as $stream) {
                    $uri = $stream->getMetadata(self::URI);
                    if (substr((string) $uri, -1) === self::DIRECTORY_SEPARATOR) {
                        continue; // Skip directories
                    }
                    $file_name = Util::sanitizeFileName($destination_path . self::DIRECTORY_SEPARATOR . basename((string) $uri));
                    file_put_contents(
                        $file_name,
                        $stream->getContents()
                    );
                }
                return true; // Stop here
        }

        $this->zip->extractTo($destination_path, iterator_to_array($this->getPaths()));

        return true;
    }

    public function hasZipReadingError(): bool
    {
        return $this->error_reading_zip;
    }
}
