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

namespace ILIAS\Export\ImportHandler\File\XML\Export\DataSet;

use ilLogger;
use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLNodeInfoAttributeFactory;
use ILIAS\Export\ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\ilFactoryInterface as ilDataSetXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\ilHandlerInterface as ilDatasetXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\File\XML\Export\DataSet\ilHandler as ilDatasetXMLExportFileHandler;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ILIAS\Export\ImportHandler\File\Validation\Set\ilFactory as ilFileValidationSetFactory;
use ILIAS\Export\Schema\ilXmlSchemaFactory;

class ilFactory implements ilDataSetXMLExportFileFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilDatasetXMLExportFileHandlerInterface
    {
        return new ilDatasetXMLExportFileHandler(
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
