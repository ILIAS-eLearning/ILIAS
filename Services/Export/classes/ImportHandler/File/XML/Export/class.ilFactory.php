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

namespace ImportHandler\File\XML\Export;

use ilLogger;
use ImportHandler\I\File\XML\Export\ilCollectionInterface as ilXMLExportFileCollectionInterface;
use ImportHandler\I\File\XML\Export\ilFactoryInterface as ilXMLExportFileFactoryInterface;
use ImportHandler\I\File\XML\Export\ilHandlerInterface as ilXMLExportFileHandlerInterface;
use ImportHandler\File\XML\Export\ilHandler as ilXMLExportFileHanlder;
use ImportStatus\ilFactory as ilImportStatusFactory;
use ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ImportHandler\Parser\ilFactory as ilParserFactory;
use ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ImportHandler\File\XML\Export\ilCollection as ilXMLExportFileHandlerCollection;
use Schema\ilXmlSchemaFactory;
use ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLNodeInfoAttributeFactory;

class ilFactory implements ilXMLExportFileFactoryInterface
{
    public ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handler(): ilXMLExportFileHandlerInterface
    {
        return new ilXMLExportFileHanlder(
            new ilImportStatusFactory(),
            new ilXmlSchemaFactory(),
            new ilParserFactory($this->logger),
            new ilXSDFileFactory(),
            new ilFilePathFactory($this->logger),
            $this->logger,
            new ilXMLNodeInfoAttributeFactory($this->logger)
        );
    }

    public function collection(): ilXMLExportFileCollectionInterface
    {
        return new ilXMLExportFileHandlerCollection();
    }
}
