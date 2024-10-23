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

namespace ILIAS\Export\ImportHandler\I\Validation\Set;

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as ilXMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as ilXSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as ilImportHandlerPathInterface;

interface HandlerInterface
{
    public function getXMLFileHandler(): ilXMLFileHandlerInterface;

    public function getFilePathHandler(): ilImportHandlerPathInterface;

    public function getXSDFileHandler(): ilXSDFileHandlerInterface;

    public function withFilePathHandler(
        ilImportHandlerPathInterface $path_handler
    ): HandlerInterface;

    public function withXSDFileHanlder(
        ilXSDFileHandlerInterface $xsd_file_handler
    ): HandlerInterface;

    public function withXMLFileHandler(
        ilXMLFileHandlerInterface $xml_file_handler
    ): HandlerInterface;
}
