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

namespace ILIAS\Export\ImportHandler\I\File;

use ILIAS\Export\ImportHandler\I\File\Namespace\CollectionInterface as ilFileNamespaceCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\HandlerInterface as ilFileNamespaceHandlerInterface;
use SplFileInfo;

interface HandlerInterface
{
    public function withAdditionalNamespace(ilFileNamespaceHandlerInterface $namespace_handler): HandlerInterface;

    public function getNamespaces(): ilFileNamespaceCollectionInterface;

    public function withFileInfo(SplFileInfo $file_info): HandlerInterface;

    public function getFileName(): string;

    public function getFilePath(): string;

    public function getPathToFileLocation(): string;

    public function getSubPathToDirBeginningAtPathStart(string $dir_name): HandlerInterface;

    public function getSubPathToDirBeginningAtPathEnd(string $dir_name): HandlerInterface;

    public function fileExists(): bool;

    public function getPathPart(string $pattern): string|null;

    public function pathContainsFolderName(string $folder_name): bool;
}
