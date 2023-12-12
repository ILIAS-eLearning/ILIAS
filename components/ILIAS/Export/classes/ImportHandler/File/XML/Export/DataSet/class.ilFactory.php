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

namespace ImportHandler\File\XML\Export\DataSet;

use ilLogger;
use ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLNodeInfoAttributeFactory;
use ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ImportHandler\I\File\XML\Export\DataSet\ilFactoryInterface as ilDataSetXMLExportFileFactoryInterface;
use ImportHandler\I\File\XML\Export\DataSet\ilHandlerInterface as ilDatasetXMLExportFileHandlerInterface;
use ImportHandler\File\XML\Export\DataSet\ilHandler as ilDatasetXMLExportFileHandler;
use ImportHandler\Parser\ilFactory as ilParserFactory;
use ImportStatus\ilFactory as ilImportStatusFactory;
use ImportHandler\File\Validation\Set\ilFactory as ilFileValidationSetFactory;
use Schema\ilXmlSchemaFactory;

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
