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

use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Zip
{
    use PathHelper;

    public const DOT_EMPTY = '.empty';
    public const ITERATION_FACTOR = 0.9;
    private string $zip_output_file = '';
    protected \ZipArchive $zip;
    private int $iteration_limit;
    private int $store_counter = 1;
    private int $path_counter = 1;

    private bool $zip_opened = false;

    /**
     * @var FileStream[]
     */
    private array $streams = [];

    public function __construct(
        protected ZipOptions $options,
        FileStream ...$streams
    ) {
        if ($options->getZipOutputPath() !== null && $options->getZipOutputName() !== null) {
            $this->zip_output_file = $this->ensureDirectorySeperator(
                $options->getZipOutputPath()
            ) . $options->getZipOutputName();
        } else {
            $this->zip_output_file = $this->buildTempPath();
            $this->registerShutdownFunction(function (): void {
                $this->destroy();
            });
        }
        $system_limit = (int) shell_exec('ulimit -n') ?: 0;

        $this->iteration_limit = $system_limit < 10 ? 100 : min(
            $system_limit / 2,
            5000
        );

        $this->zip = new \ZipArchive();
        if (!file_exists($this->zip_output_file)) {
            touch($this->zip_output_file);
        }

        $this->maybeOpenZip(\ZipArchive::OVERWRITE);
        foreach ($streams as $path_inside_zip => $stream) {
            $path_inside_zip = is_int($path_inside_zip) ? basename((string) $stream->getMetadata('uri')) : $path_inside_zip;
            $this->addStream($stream, basename($path_inside_zip));
        }
    }

    private function maybeOpenZip(int $flags = 0): void
    {
        if (!$this->zip_opened) {
            if ($flags === 0) {
                $this->zip_opened = $this->zip->open($this->zip_output_file) === true;
            } else {
                $this->zip_opened = $this->zip->open($this->zip_output_file, $flags) === true;
            }
        }
        if (!$this->zip_opened) {
            throw new \Exception("cannot open <$this->zip_output_file>\n");
        }
    }

    private function buildTempPath(): string
    {
        $directory = defined('CLIENT_DATA_DIR') ? \CLIENT_DATA_DIR . '/temp' : sys_get_temp_dir();
        $tempnam = tempnam($directory, 'zip');
        if (is_file($tempnam)) {
            return $tempnam;
        }
        if (is_dir($tempnam)) {
            rmdir($tempnam);
            touch($tempnam);
        }
        return $tempnam;
    }

    private function registerShutdownFunction(\Closure $c): void
    {
        register_shutdown_function($c);
    }

    private function storeZIPtoFilesystem(): void
    {
        foreach ($this->streams as $path_inside_zip => $stream) {
            $path = $stream->getMetadata('uri');
            if ($this->store_counter === 0) {
                $this->maybeOpenZip();
            }
            if (is_int($path_inside_zip)) {
                $path_inside_zip = basename((string) $path);
            }

            if ($path === 'php://memory') {
                $this->zip->addFromString($path_inside_zip, (string) $stream);
                $stream->close();
            } elseif (is_file($path)) {
                $this->zip->addFile($path, $path_inside_zip);
                $stream->close();
            } else {
                continue;
            }

            if (
                $this->store_counter === $this->iteration_limit
                || count(get_resources('stream')) > ($this->iteration_limit * self::ITERATION_FACTOR)
            ) {
                $this->zip->close();
                $this->zip_opened = false;
                $this->store_counter = 0;
            } else {
                $this->store_counter++;
            }
        }
    }

    public function get(): Stream
    {
        $this->maybeOpenZip();
        $this->storeZIPtoFilesystem();

        $this->zip->close();
        $this->zip_opened = false;

        return Streams::ofResource(fopen($this->zip_output_file, 'rb'));
    }

    /**
     * @description Explicitly close the zip file and remove the file from the filesystem. In general, temp
     * files are deleted whyle destroying the object. but in cases like migrations, you should call this method explicitly.
     * Please note that also explicitly set paths (non-temp) are deleted if you call this method.
     */
    public function destroy(): void
    {
        if (file_exists($this->zip_output_file)) {
            unlink($this->zip_output_file);
        }
    }

    /**
     * @deprecated in general, it should be avoided to operate with correct paths in the file system.
     * it is also usually not necessary to zip whole directories, as a ZIP can be seen as an "on-the-fly" compilation
     * of different streams. However, since ILIAS still relies on zipping entire directories in many places, this
     * method is still offered for the moment.
     */
    public function addPath(string $path, ?string $path_inside_zip = null): void
    {
        $path_inside_zip ??= basename($path);

        $this->maybeOpenZip();

        // create directory if it does not exist
        $this->zip->addEmptyDir(rtrim(dirname($path_inside_zip), '/') . '/');

        $this->addStream(
            Streams::ofResource(fopen($path, 'rb')),
            $path_inside_zip
        );
    }

    public function addStream(FileStream $stream, string $path_inside_zip): void
    {
        // we remove the "empty zip file" now if possible
        if (isset($this->streams[self::DOT_EMPTY])) {
            unset($this->streams[self::DOT_EMPTY]);
        }

        // we must store the ZIP to e temporary files every 1000 files, otherwise we will get a Too Many Open Files error
        $this->streams[$path_inside_zip] = $stream;

        if (
            $this->path_counter === $this->iteration_limit
            || count(get_resources('stream')) > ($this->iteration_limit * self::ITERATION_FACTOR)
        ) {
            $this->storeZIPtoFilesystem();
            $this->streams = [];
            $this->path_counter = 0;
        } else {
            $this->path_counter++;
        }
    }

    /**
     * @deprecated in general, it should be avoided to operate with correct paths in the file system.
     * it is also usually not necessary to zip whole directories, as a ZIP can be seen as an "on-the-fly" compilation
     * of different streams. However, since ILIAS still relies on zipping entire directories in many places, this
     * method is still offered for the moment.
     */
    public function addDirectory(string $directory_to_zip): void
    {
        $directory_to_zip = $this->normalizePath(rtrim($directory_to_zip, '/'));
        // find all files in the directory recursively
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory_to_zip),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        switch ($this->options->getDirectoryHandling()) {
            case ZipDirectoryHandling::KEEP_STRUCTURE:
                $pattern = null;
                $prefix = '';
                break;
            case ZipDirectoryHandling::ENSURE_SINGLE_TOP_DIR:
                $prefix = basename($directory_to_zip) . '/';
                $pattern = '/^' . preg_quote($prefix, '/') . '/';
                break;
        }

        foreach ($files as $file) {
            $pathname = $file->getPathname();
            $path_inside_zip = str_replace($directory_to_zip . '/', '', $pathname);
            if ($pattern !== null) {
                $path_inside_zip = $prefix . preg_replace($pattern, '', $path_inside_zip);
            }

            /** @var $file \SplFileInfo */
            if ($file->isDir()) {
                // add directory to zip if it's empty
                $sub_items = array_filter(scandir($pathname), static fn($d): bool => !str_contains($d, '.DS_Store'));
                if (count($sub_items) === 2) {
                    $this->zip->addEmptyDir($path_inside_zip);
                }
                continue;
            }

            if ($this->isPathIgnored($pathname, $this->options)) {
                continue;
            }

            $this->addPath(realpath($pathname), $path_inside_zip);
        }
    }
}
