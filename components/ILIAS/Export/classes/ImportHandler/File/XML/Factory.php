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

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Export\ImportHandler\File\XML\Collection as ilImportHandlerXMLFileCollection;
use ILIAS\Export\ImportHandler\File\XML\Export\Factory as ilImportHandlerXMLExportFileFactory;
use ILIAS\Export\ImportHandler\File\XML\Handler as ilImportHandlerXMLFile;
use ILIAS\Export\ImportHandler\File\XML\Manifest\Factory as ilImportHandlerManifestFileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\CollectionInterface as ilImportHandlerXMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\FactoryInterface as ilImportHandlerXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\FactoryInterface as ilImportHandlerXMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as ilImportHandlerXMLFileInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\FactoryInterface as ilImportHandlerManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as ilImportHandlerSchemaFactoryInterface;
use ILIAS\Export\ImportHandler\Schema\Factory as ilImportHandlerSchemaFactory;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ilLanguage;
use ilLogger;

class Factory implements ilImportHandlerXMLFileFactoryInterface
{
    protected ilImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ilImportStatusFactory $import_status_factory;
    protected ilDataFactory $data_factory;

    public function __construct(
        ilImportHandlerFactoryInterface $import_handler,
        ilImportStatusFactory $import_status_factory,
        ilLogger $logger,
        ilLanguage $lng,
        ilDataFactory $data_factory
    ) {
        $this->import_handler = $import_handler;
        $this->logger = $logger;
        $this->lng = $lng;
        $this->data_factory = $data_factory;
    }

    public function handler(): ilImportHandlerXMLFileInterface
    {
        return new ilImportHandlerXMLFile(
            $this->import_handler->file()->namespace(),
            $this->import_status_factory
        );
    }

    public function collection(): ilImportHandlerXMLFileCollectionInterface
    {
        return new ilImportHandlerXMLFileCollection();
    }

    public function manifest(): ilImportHandlerManifestFileFactoryInterface
    {
        return new ilImportHandlerManifestFileFactory(
            $this->import_handler,
            $this->logger,
            $this->import_status_factory
        );
    }

    public function export(): ilImportHandlerXMLExportFileFactoryInterface
    {
        return new ilImportHandlerXMLExportFileFactory(
            $this->import_handler,
            $this->logger,
            $this->lng
        );
    }

    public function schema(): ilImportHandlerSchemaFactoryInterface
    {
        return new ilImportHandlerSchemaFactory(
            $this->import_handler,
            $this->data_factory,
            $this->logger
        );
    }
}
