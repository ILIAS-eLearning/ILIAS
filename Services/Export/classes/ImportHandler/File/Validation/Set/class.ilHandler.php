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

namespace ILIAS\Export\ImportHandler\File\Validation\Set;

use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilHandlerInterface as ilFileValidationSetHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;

class ilHandler implements ilFileValidationSetHandlerInterface
{
    protected ilXSDFileHandlerInterface $xsd_file_handler;
    protected ilFilePathHandlerInterface $path_handler;
    protected ilXMLFileHandlerInterface $xml_file_handler;

    public function getFilePathHandler(): ilFilePathHandlerInterface
    {
        return $this->path_handler;
    }

    public function getXSDFileHandler(): ilXSDFileHandlerInterface
    {
        return $this->xsd_file_handler;
    }

    public function getXMLFileHandler(): ilXMLFileHandlerInterface
    {
        return $this->xml_file_handler;
    }

    public function withFilePathHandler(ilFilePathHandlerInterface $path_handler): ilFileValidationSetHandlerInterface
    {
        $clone = clone $this;
        $clone->path_handler = $path_handler;
        return $clone;
    }

    public function withXSDFileHanlder(ilXSDFileHandlerInterface $xsd_file_handler): ilFileValidationSetHandlerInterface
    {
        $clone = clone $this;
        $clone->xsd_file_handler = $xsd_file_handler;
        return $clone;
    }

    public function withXMLFileHandler(ilXMLFileHandlerInterface $xml_file_handler): ilFileValidationSetHandlerInterface
    {
        $clone = clone $this;
        $clone->xml_file_handler = $xml_file_handler;
        return $clone;
    }
}
