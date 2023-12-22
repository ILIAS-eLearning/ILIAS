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

namespace ILIAS\Export\ImportHandler\I\File\Validation;

use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilParserPathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilCollectionInterface as ilFileValidationSetCollectionInterface;

interface ilHandlerInterface
{
    public function validateXMLFile(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler
    ): ilImportStatusHandlerCollectionInterface;

    public function validateXMLAtPath(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler,
        ilParserPathHandlerInterface $path_handler
    ): ilImportStatusHandlerCollectionInterface;

    public function validateSets(
        ilFileValidationSetCollectionInterface $sets
    ): ilImportStatusHandlerCollectionInterface;
}
