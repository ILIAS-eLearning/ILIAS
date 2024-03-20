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

use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ILIAS\Export\ImportHandler\File\Validation\Set\ilFactory as ilFileValidationSetFactory;
use ILIAS\Export\ImportHandler\File\XML\Export\Component\ilHandler as ilComponentXMLExportFileHandler;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLNodeInfoAttributeFactory;
use ILIAS\Export\ImportHandler\File\XML\Schema\ilFactory as ilXMLFileSchemaFactory;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\ilFactoryInterface as ilComponentXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\ilHandlerInterface as ilComponentXMLExportFileHandlerInterface;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ILIAS\Export\Schema\ilXmlSchemaFactory;
use ilLanguage;
use ilLogger;

class ilFactory implements ilComponentXMLExportFileFactoryInterface
{
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ilXmlSchemaFactory $schema_factory;

    public function __construct(
        ilLogger $logger,
        ilLanguage $lng,
        ilXmlSchemaFactory $schema_factory
    ) {
        $this->logger = $logger;
        $this->lng = $lng;
        $this->schema_factory = $schema_factory;
    }

    public function handler(): ilComponentXMLExportFileHandlerInterface
    {
        return new ilComponentXMLExportFileHandler(
            new ilFileNamespaceFactory(),
            new ilImportStatusFactory(),
            new ilXMLFileSchemaFactory(
                $this->logger,
                $this->lng,
                $this->schema_factory
            ),
            new ilParserFactory($this->logger),
            new ilFilePathFactory($this->logger),
            $this->logger,
            new ilXMLNodeInfoAttributeFactory($this->logger),
            new ilFileValidationSetFactory(),
            $this->lng
        );
    }
}
