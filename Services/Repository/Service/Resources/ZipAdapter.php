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

namespace ILIAS\Repository\Resources;

use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Export\ImportStatus\Exception\ilException;
use ILIAS\Filesystem\Util\Archive\LegacyArchives;
use ILIAS\Filesystem\Util\Archive\ZipDirectoryHandling;
use RuntimeException;

class ZipAdapter
{
    protected Archives $archives;
    protected LegacyArchives $legacy_archives;
    protected \ilFileServicesSettings $file_service_settings;

    public function __construct(
        Archives $archives,
        LegacyArchives $legacy_archives,
        \ilFileServicesSettings $file_service_settings
    ) {
        $this->archives = $archives;
        $this->legacy_archives = $legacy_archives;
        $this->file_service_settings = $file_service_settings;
    }

    public function unzipFile(string $filepath): void
    {
        $destination_path = dirname($filepath);
        $temporary_directory = $this->createTemporaryExtractionDirectory();

        try {
            $unzip = $this->archives->unzip(
                Streams::ofResource(fopen($filepath, 'rb')),
                $this->archives->unzipOptions()
                    ->withZipOutputPath($temporary_directory)
                    ->withOverwrite(false)
                    ->withDirectoryHandling(ZipDirectoryHandling::KEEP_STRUCTURE)
            );

            foreach (iterator_to_array($unzip->getPaths(), false) as $zip_path) {
                $this->assertZipPathIsSafe($zip_path);
            }

            if (!$unzip->extract()) {
                throw new ilException("Unzip failed.");
            }

            foreach (iterator_to_array($unzip->getFiles(), false) as $zip_file) {
                if (!$this->isWhitelistedFile($zip_file)) {
                    continue;
                }
                $this->moveExtractedFile($temporary_directory, $destination_path, $zip_file);
            }
        } finally {
            $this->removeDirectory($temporary_directory);
        }
    }

    protected function createTemporaryExtractionDirectory(): string
    {
        $tmp_directory = rtrim(CLIENT_DATA_DIR, '/') . '/temp/' .
            'tmp_' . bin2hex(random_bytes(16));
        \ilFileUtils::makeDirParents($tmp_directory);
        return $tmp_directory;
    }

    protected function assertZipPathIsSafe(string $path): void
    {
        $normalized_path = $this->normalizeZipPath($path);

        if (
            $normalized_path === ''
            || str_contains($normalized_path, "\0")
            || str_starts_with($normalized_path, '/')
            || preg_match('/^[A-Za-z]:\//', $normalized_path) === 1
        ) {
            throw new ilException('Zip contains an unsafe path.');
        }

        foreach (explode('/', rtrim($normalized_path, '/')) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new ilException('Zip contains an unsafe path.');
            }
        }
    }

    protected function isWhitelistedFile(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, $this->file_service_settings->getWhiteListedSuffixes(), true);
    }

    protected function moveExtractedFile(string $temporary_directory, string $destination_path, string $zip_file): void
    {
        $normalized_zip_file = $this->normalizeZipPath($zip_file);
        $source = $temporary_directory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized_zip_file);
        $target = $destination_path . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized_zip_file);

        if (!is_file($source)) {
            return;
        }

        $target_directory = dirname($target);
        if (!is_dir($target_directory)) {
            \ilFileUtils::makeDirParents($target_directory);
        }

        if (file_exists($target)) {
            return;
        }

        if (!rename($source, $target)) {
            throw new RuntimeException(sprintf('File "%s" could not be moved to "%s"', $source, $target));
        }
    }

    protected function normalizeZipPath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    protected function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        \ilFileUtils::delDir($directory);
    }

    public function zipDirectoryToFile(string $directory, string $zip_file): void
    {
        $this->legacy_archives->zip(
            $directory,
            $zip_file,
            true
        );
    }
}
