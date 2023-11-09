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

namespace ILIAS\Setup\CLI;

use ZipArchive;
use ILIAS\Setup;
use FilesystemIterator;
use ILIAS\Setup\Environment;
use RecursiveIteratorIterator;
use ilIniFilesLoadedObjective;
use RecursiveDirectoryIterator;

class ImportFileUnzippedFileObjective implements Setup\Objective
{
    protected string $zip_path;

    public function __construct(string $zip_path)
    {
        $this->zip_path = $zip_path;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Unzip files from $this->zip_path into temporary directory";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $tmp_dir = $environment->getConfigFor("tmp_dir");
        $dirs = [
            $tmp_dir . DIRECTORY_SEPARATOR . "web_data.zip" => $tmp_dir . DIRECTORY_SEPARATOR . "web_data",
            $tmp_dir . DIRECTORY_SEPARATOR . "Customizing.zip" => $tmp_dir . DIRECTORY_SEPARATOR . "Customizing",
            $tmp_dir . DIRECTORY_SEPARATOR . "data.zip" => $tmp_dir . DIRECTORY_SEPARATOR . "data",
            $tmp_dir . DIRECTORY_SEPARATOR . "dump.zip" => $tmp_dir
        ];

        $this->extractZip($this->zip_path, $tmp_dir);

        foreach ($dirs as $source => $destination) {
            if (!file_exists($source)) {
                continue;
            }

            $this->extractZip($source, $destination);

            $this->deleteRecursive($source);
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return file_exists($this->zip_path);
    }

    protected function deleteRecursive(string $path, bool $delete_base_dir = false): void
    {
        if (is_file($path)) {
            unlink($path);
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file_info) {
            if ($file_info->isDir()) {
                rmdir($file_info->getRealPath());
                continue;
            }
            unlink($file_info->getRealPath());
        }

        if ($delete_base_dir) {
            rmdir($path);
        }
    }

    protected function extractZip(string $source, string $destination): void
    {
        $zip = new ZipArchive();
        try {
            $zip->open($source);
            $zip->extractTo($destination);
        } catch (\Exception $e) {
            throw new Setup\UnachievableException("Could not open zip at $source");
        } finally {
            $zip->close();
        }
    }
}
