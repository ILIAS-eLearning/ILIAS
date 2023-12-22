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

namespace Test\ImportHandler\File\Validation\Set;

use PHPUnit\Framework\TestCase;
use ILIAS\Export\ImportHandler\File\Validation\Set\ilHandler as ilFileValidationSetHandler;
use ILIAS\Export\ImportHandler\File\Path\ilHandler as ilFilePathHandler;
use ILIAS\Export\ImportHandler\File\XML\ilHandler as ilXMLFileHandler;
use ILIAS\Export\ImportHandler\File\XSD\ilHandler as ilXSDFileHandler;

class ilHandlerTest extends TestCase
{
    public function testFileValidationSetHandler(): void
    {
        $xsd_file = $this->createMock(ilXSDFileHandler::class);
        $xml_file = $this->createMock(ilXMLFileHandler::class);
        $file_path = $this->createMock(ilFilePathHandler::class);

        $set = (new ilFileValidationSetHandler())
            ->withFilePathHandler($file_path)
            ->withXMLFileHandler($xml_file)
            ->withXSDFileHanlder($xsd_file);

        $this->assertEquals($file_path, $set->getFilePathHandler());
        $this->assertEquals($xsd_file, $set->getXSDFileHandler());
        $this->assertEquals($xml_file, $set->getXMLFileHandler());
    }
}
