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

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\HandlerInterface as XSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;

interface HandlerInterface
{
    public function getXMLFileHandler(): XMLFileHandlerInterface;

    public function getFilePathHandler(): PathInterface;

    public function getXSDFileHandler(): XSDFileHandlerInterface;

    public function withFilePathHandler(
        PathInterface $path_handler
    ): HandlerInterface;

    public function withXSDFileHanlder(
        XSDFileHandlerInterface $xsd_file_handler
    ): HandlerInterface;

    public function withXMLFileHandler(
        XMLFileHandlerInterface $xml_file_handler
    ): HandlerInterface;
}
