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

namespace ILIAS\Export\ImportHandler\File\XML;

use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\XML\Export\ilFactory as ilXMLExportFileFactory;
use ILIAS\Export\ImportHandler\File\XML\ilCollection as ilXMLFileHanlderCollection;
use ILIAS\Export\ImportHandler\File\XML\ilHandler as ilXMLFileHanlder;
use ILIAS\Export\ImportHandler\File\XML\Manifest\ilFactory as ilManifestFileFactory;
use ILIAS\Export\ImportHandler\File\XML\Node\ilFactory as ilXMLFileNodeFactory;
use ILIAS\Export\ImportHandler\File\XML\Schema\ilFactory as ilXMLFileSchemaFactory;
use ILIAS\Export\ImportHandler\I\File\XML\Export\ilFactoryInterface as ilXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilCollectionInterface as ilXMLFileHanlderCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilFactoryInterface as ilXMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHanlderInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\ilFactoryInterface as ilManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\ilFactoryInterface as ilXMLFileNodeFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Schema\ilFactoryInterface as ilXMLFileSchemaFactoryInterface;
use ILIAS\Export\ImportStatus\ilFactory as ilStatusFactory;
use ILIAS\Export\Schema\ilXmlSchemaFactory;
use ilLanguage;
use ilLogger;
use SplFileInfo;

class ilFactory implements ilXMLFileFactoryInterface
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

    public function withFileInfo(SplFileInfo $file_info): ilXMLFileHanlderInterface
    {
        return (new ilXMLFileHanlder(
            new ilFileNamespaceFactory(),
            new ilStatusFactory()
        ))->withFileInfo($file_info);
    }

    public function collection(): ilXMLFileHanlderCollectionInterface
    {
        return new ilXMLFileHanlderCollection();
    }

    public function manifest(): ilManifestFileFactoryInterface
    {
        return new ilManifestFileFactory(
            $this->logger,
            $this->lng,
            $this->schema_factory
        );
    }

    public function node(): ilXMLFileNodeFactoryInterface
    {
        return new ilXMLFileNodeFactory($this->logger);
    }

    public function export(): ilXMLExportFileFactoryInterface
    {
        return new ilXMLExportFileFactory(
            $this->logger,
            $this->lng,
            $this->schema_factory
        );
    }

    public function schema(): ilXMLFileSchemaFactoryInterface
    {
        return new ilXMLFileSchemaFactory(
            $this->logger,
            $this->lng,
            $this->schema_factory
        );
    }
}
