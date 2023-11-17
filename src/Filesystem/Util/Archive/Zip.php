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

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Util\Archive\BaseZip;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Zip
{
    use PathHelper;

    private string $zip_output_file = '';
    protected \ZipArchive $zip;
    private int $iteration_limit;
    private int $store_counter = 1;
    private int $path_counter = 1;

    /**
     * @var FileStream[]
     */
    private array $streams;

    public function __construct(
        protected ZipOptions $options,
        ...$streams
    ) {
        $this->streams = array_filter($streams, function ($stream): bool {
            return $stream instanceof FileStream;
        });

        if ($options->getZipOutputPath() !== null && $options->getZipOutputName() !== null) {
            $this->zip_output_file = $this->ensureDirectorySeperator(
                $options->getZipOutputPath()
            ) . $options->getZipOutputName();
        } else {
            $this->zip_output_file = is_writable('php://temp') ? 'php://temp' : $this->buildTempPath();
            $this->registerShutdownFunction(function (): void {
                if (file_exists($this->zip_output_file)) {
                    unlink($this->zip_output_file);
                }
            });
        }
        $system_limit = (int) shell_exec('ulimit -n') ?: 0;

        if ($system_limit < 10) { //  aka we cannot determine the system limit properly
            $this->iteration_limit = 100;
        } else {
            $this->iteration_limit = min(
                $system_limit / 2,
                5000
            );
        }

        $this->zip = new \ZipArchive();
        if (!file_exists($this->zip_output_file)) {
            touch($this->zip_output_file);
        }
        if ($this->zip->open($this->zip_output_file, \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("cannot open <$this->zip_output_file>\n");
        }
    }

    private function buildTempPath(): string
    {
        return tempnam(sys_get_temp_dir(), 'zip');
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
                $this->zip->open($this->zip_output_file);
            }
            if (is_int($path_inside_zip)) {
                $path_inside_zip = basename($path);
            }

            if ($path === 'php://memory') {
                $this->zip->addFromString($path_inside_zip, (string) $stream);
                $stream->close();
            } else {
                $this->zip->addFile($path, $path_inside_zip);
                $stream->close();
            }

            if (
                $this->store_counter === $this->iteration_limit
                || count(get_resources('stream')) > ($this->iteration_limit * 0.9)
            ) {
                $this->zip->close();
                $this->store_counter = 0;
            } else {
                $this->store_counter++;
            }
        }
    }


    public function get(): \ILIAS\Filesystem\Stream\Stream
    {
        $this->storeZIPtoFilesystem();

        $this->zip->close();

        return Streams::ofResource(fopen($this->zip_output_file, 'rb'));
    }

    /**
     * @deprecated in general, it should be avoided to operate with correct paths in the file system.
     * it is also usually not necessary to zip whole directories, as a ZIP can be seen as an "on-the-fly" compilation
     * of different streams. However, since ILIAS still relies on zipping entire directories in many places, this
     * method is still offered for the moment.
     */
    public function addPath(string $path, ?string $path_inside_zip = null): void
    {
        $this->addStream(
            Streams::ofResource(fopen($path, 'r')),
            $path_inside_zip ?? basename($path)
        );
    }

    public function addStream(FileStream $stream, string $path_inside_zip): void
    {
        // we must store the ZIP to e temporary files every 1000 files, otherwise we will get a Too Many Open Files error
        $this->streams[$path_inside_zip] = $stream;

        if (
            $this->path_counter === $this->iteration_limit
            || count(get_resources('stream')) > ($this->iteration_limit * 0.9)
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

        foreach ($files as $file) {
            /** @var $file \SplFileInfo */
            if ($file->isDir()) {
                continue;
            }
            $pathname = $file->getPathname();
            if ($this->isPathIgnored($pathname, $this->options)) {
                continue;
            }

            $path_inside_zip = str_replace($directory_to_zip . '/', '', $pathname);
            $this->addPath(realpath($pathname), $path_inside_zip);
        }
    }
}
