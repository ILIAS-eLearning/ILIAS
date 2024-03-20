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

namespace ILIAS\Export\ImportHandler\File\XML\Manifest;

use ILIAS\Export\ImportHandler\File\ilFactory as ilFileFactory;
use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\XML\Manifest\ilHandler as ilManifestXMLFileHandler;
use ILIAS\Export\ImportHandler\File\XML\Manifest\ilHandlerCollection as ilManifestXMLFileHandlerCollection;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\ilFactoryInterface as ilManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\ilHandlerCollectionInterface as ilManifestXMLFileHandlerCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\ilHandlerInterface as ilManifestXMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ILIAS\Export\Schema\ilXmlSchemaFactory;
use ilLanguage;
use ilLogger;
use SplFileInfo;

class ilFactory implements ilManifestFileFactoryInterface
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

    public function handler(): ilManifestXMLFileHandlerInterface
    {
        return new ilManifestXMLFileHandler(
            new ilFileNamespaceFactory(),
            $this->schema_factory,
            new ilImportStatusFactory(),
            new ilFileFactory($this->logger, $this->lng, $this->schema_factory),
            new ilParserFactory($this->logger),
            $this->logger,
        );
    }

    public function withFileInfo(SplFileInfo $file_info): ilManifestXMLFileHandlerInterface
    {
        return (new ilManifestXMLFileHandler(
            new ilFileNamespaceFactory(),
            $this->schema_factory,
            new ilImportStatusFactory(),
            new ilFileFactory($this->logger, $this->lng, $this->schema_factory),
            new ilParserFactory($this->logger),
            $this->logger,
        ))->withFileInfo($file_info);
    }

    public function handlerCollection(): ilManifestXMLFileHandlerCollectionInterface
    {
        return new ilManifestXMLFileHandlerCollection(
            new ilImportStatusFactory()
        );
    }
}
