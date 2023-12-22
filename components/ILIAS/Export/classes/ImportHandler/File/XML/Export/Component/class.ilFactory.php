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

namespace ILIAS\Export\ImportHandler\File\XML\Export\Component;

use ilLogger;
use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLNodeInfoAttributeFactory;
use ILIAS\Export\ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ILIAS\Export\ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\ilFactoryInterface as ilComponentXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\ilHandlerInterface as ilComponentXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\File\XML\Export\Component\ilHandler as ilComponentXMLExportFileHandler;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ILIAS\Export\Schema\ilXmlSchemaFactory;
use ILIAS\Export\ImportHandler\File\Validation\Set\ilFactory as ilFileValidationSetFactory;

class ilFactory implements ilComponentXMLExportFileFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilComponentXMLExportFileHandlerInterface
    {
        return new ilComponentXMLExportFileHandler(
            new ilFileNamespaceFactory(),
            new ilImportStatusFactory(),
            new ilXmlSchemaFactory(),
            new ilParserFactory($this->logger),
            new ilXSDFileFactory(),
            new ilFilePathFactory($this->logger),
            $this->logger,
            new ilXMLNodeInfoAttributeFactory($this->logger),
            new ilFileValidationSetFactory()
        );
    }
}
