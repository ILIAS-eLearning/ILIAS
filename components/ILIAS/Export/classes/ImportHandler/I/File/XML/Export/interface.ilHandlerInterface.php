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

namespace ImportHandler\I\File\XML\Export;

use ILIAS\Data\Version;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ImportStatus\I\ilCollectionInterface as ilImportStatusCollectionInterface;
use SplFileInfo;

interface ilHandlerInterface extends ilXMLFileHandlerInterface
{
    public function getXSDFileHandler(): ilXSDFileHandlerInterface|null;

    public function getVersion(): Version;

    public function getType(): string;

    public function getSubType(): string;

    public function getILIASPath(ilXMLFileNodeInfoTreeInterface $component_tree): string;

    public function withFileInfo(SplFileInfo $file_info): ilHandlerInterface;

    public function loadExportInfo(): ilImportStatusCollectionInterface;

    public function isContainerExportXML(): bool;
}
