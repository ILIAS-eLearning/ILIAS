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

namespace ILIAS\Export\ImportHandler\Validation\Set;

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as FilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\Validation\Set\HandlerInterface as FileValidationSetHandlerInterface;

class Handler implements FileValidationSetHandlerInterface
{
    protected XSDFileHandlerInterface $xsd_file_handler;
    protected FilePathHandlerInterface $path_handler;
    protected XMLFileHandlerInterface $xml_file_handler;

    public function getFilePathHandler(): FilePathHandlerInterface
    {
        return $this->path_handler;
    }

    public function getXSDFileHandler(): XSDFileHandlerInterface
    {
        return $this->xsd_file_handler;
    }

    public function getXMLFileHandler(): XMLFileHandlerInterface
    {
        return $this->xml_file_handler;
    }

    public function withFilePathHandler(
        FilePathHandlerInterface $path_handler
    ): FileValidationSetHandlerInterface {
        $clone = clone $this;
        $clone->path_handler = $path_handler;
        return $clone;
    }

    public function withXSDFileHanlder(
        XSDFileHandlerInterface $xsd_file_handler
    ): FileValidationSetHandlerInterface {
        $clone = clone $this;
        $clone->xsd_file_handler = $xsd_file_handler;
        return $clone;
    }

    public function withXMLFileHandler(
        XMLFileHandlerInterface $xml_file_handler
    ): FileValidationSetHandlerInterface {
        $clone = clone $this;
        $clone->xml_file_handler = $xml_file_handler;
        return $clone;
    }
}
