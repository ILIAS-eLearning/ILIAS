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

namespace ILIAS\Export\ImportHandler\I\File\XML\Export;

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Tree\HandlerInterface as ParserNodeInfoTreeInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;
use ILIAS\Export\ImportHandler\I\Validation\Set\CollectionInterface as FileValidationSetCollectionInterface;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ImportStatusCollectionInterface;
use SplFileInfo;

interface HandlerInterface extends XMLFileHandlerInterface
{
    public function getValidationSets(): FileValidationSetCollectionInterface;

    public function buildValidationSets(): ImportStatusCollectionInterface;

    public function getPathToComponentRootNodes(): PathInterface;

    public function pathToExportNode(): PathInterface;

    public function getILIASPath(ParserNodeInfoTreeInterface $component_tree): string;

    public function withFileInfo(SplFileInfo $file_info): HandlerInterface;

    public function isContainerExportXML(): bool;

    public function hasComponentRootNode(): bool;

}
