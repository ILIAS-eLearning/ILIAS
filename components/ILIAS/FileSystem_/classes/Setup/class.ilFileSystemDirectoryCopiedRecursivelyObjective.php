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

use ILIAS\Setup;

class ilFileSystemDirectoryCopiedRecursivelyObjective implements Setup\Objective
{
    protected string $source_folder;
    protected string $target_folder;
    protected bool $data_dir;
    protected bool $bare;

    /**
     * Copies a directory from ILIAS root or from the outer ILIAS data directory
     * depending on the flag $data_dir.
     * With $source_folder you can select the directory to copy from selected
     * root directory. Empty string for $source_folder means copy the whole root directory.
     * $target_folder should always be the path wehre to copy into.
     * Set the bare flag true to copy from $source_folder to $target_folder on whole filesystem.
     */
    public function __construct(
        string $source_folder,
        string $target_folder,
        bool $data_dir = false,
        bool $bare = false
    ) {
        $this->source_folder = $source_folder;
        $this->target_folder = $target_folder;
        $this->data_dir = $data_dir;
        $this->bare = $bare;
    }

    /**
     * @inheritdoc
     */
    public function getHash(): string
    {
        return hash("sha256", self::class . $this->getSourceName($this->source_folder) . $this->target_folder);
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        $source = $this->getSourceName($this->source_folder);
        return "Copy directory from $source to $this->target_folder.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $source = $this->source_folder;

        if (!$this->bare) {
            /** @var ilIniFile $ini */
            $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

            $root = $ini->readVariable("server", "absolute_path");

            if ($this->data_dir) {
                $root = $ini->readVariable("clients", "datadir");
            }

            $source = $root . DIRECTORY_SEPARATOR . $this->source_folder;
        }

        if (file_exists($this->target_folder)) {
            $this->deleteDirRecursive($this->target_folder);
        } else {
            mkdir($this->target_folder, 0755);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($this->target_folder . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
            } else {
                copy($item->getPathname(), $this->target_folder . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
            }
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $source = $this->source_folder;

        if (!$this->bare) {
            /** @var ilIniFile $ini */
            $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

            $root = $ini->readVariable("server", "absolute_path");
            if ($this->data_dir) {
                $root = $ini->readVariable("clients", "datadir");
            }

            $source = $root . DIRECTORY_SEPARATOR . $this->source_folder;
        }

        return
            file_exists($source) &&
            is_writable(pathinfo($this->target_folder, PATHINFO_DIRNAME))
        ;
    }

    protected function deleteDirRecursive(string $path): void
    {
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
    }

    protected function getSourceName(string $source): string
    {
        if ($source !== "") {
            return $source;
        }

        if ($this->data_dir) {
            return "ilias data dir";
        }

        return "ilias root";
    }
}
