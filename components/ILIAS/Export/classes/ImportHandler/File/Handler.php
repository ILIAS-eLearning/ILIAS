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

namespace ILIAS\Export\ImportHandler\File;

use ILIAS\Export\ImportHandler\I\File\HandlerInterface as FileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\CollectionInterface as FileNamespaceCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as FileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\HandlerInterface as FileNamespaceHandlerInterface;
use SplFileInfo;

class Handler implements FileHandlerInterface
{
    protected SplFileInfo $spl_file_info;
    protected FileNamespaceFactoryInterface $namespace;
    protected FileNamespaceCollectionInterface $namespaces;

    public function __construct(
        FileNamespaceFactoryInterface $namespace
    ) {
        $this->namespace = $namespace;
        $this->namespaces = $namespace->collection();
    }

    public function withAdditionalNamespace(FileNamespaceHandlerInterface $namespace_handler): FileHandlerInterface
    {
        $clone = clone $this;
        $clone->namespaces = $clone->namespaces->withElement($namespace_handler);
        return $clone;
    }

    public function getNamespaces(): FileNamespaceCollectionInterface
    {
        return $this->namespaces;
    }

    public function withFileInfo(SplFileInfo $file_info): FileHandlerInterface
    {
        $clone = clone $this;
        $clone->spl_file_info = $file_info;
        return $clone;
    }

    public function getFileName(): string
    {
        return $this->spl_file_info->getFilename();
    }

    public function getFilePath(): string
    {
        return $this->fileExists()
            ? $this->spl_file_info->getRealPath()
            : $this->spl_file_info->getPath() . DIRECTORY_SEPARATOR . $this->spl_file_info->getFilename();
    }

    public function getSubPathToDirBeginningAtPathEnd(string $dir_name): FileHandlerInterface
    {
        $parts = explode(DIRECTORY_SEPARATOR, $this->getFilePath());
        $trimmed_str = '';
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            $trimmed_str = $i < count($parts) - 1
                ? $parts[$i] . DIRECTORY_SEPARATOR . $trimmed_str
                : $parts[$i];
            if ($parts[$i] === $dir_name) {
                break;
            }
        }
        $clone = clone $this;
        $clone->spl_file_info = new SplFileInfo($trimmed_str);
        return $clone;
    }

    public function getSubPathToDirBeginningAtPathStart(string $dir_name): FileHandlerInterface
    {
        $parts = explode(DIRECTORY_SEPARATOR, $this->getFilePath());
        $trimmed_str = '';
        for ($i = 0; $i < count($parts); $i++) {
            $trimmed_str .= $i > 0
                ? DIRECTORY_SEPARATOR . $parts[$i]
                : $parts[$i];
            if ($parts[$i] === $dir_name) {
                break;
            }
        }
        $clone = clone $this;
        $clone->spl_file_info = new SplFileInfo($trimmed_str);
        return $clone;
    }

    public function getPathToFileLocation(): string
    {
        return $this->spl_file_info->getPath();
    }

    public function fileExists(): bool
    {
        return $this->spl_file_info->getRealPath() !== false;
    }

    public function getPathPart(string $pattern): string|null
    {
        $path_parts = explode(DIRECTORY_SEPARATOR, $this->getFilePath());
        foreach ($path_parts as $path_part) {
            if (preg_match($pattern, $path_part) === 1) {
                return $path_part;
            }
        }
        return null;
    }

    public function pathContainsFolderName(string $folder_name): bool
    {
        $path_parts = explode(DIRECTORY_SEPARATOR, $this->getFilePath());
        if (in_array($folder_name, $path_parts, true)) {
            return true;
        }
        return false;
    }
}
