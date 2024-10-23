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

use ILIAS\Export\ImportHandler\File\XML\Manifest\Collection as ilImportHandlerManifestXMLFileCollection;
use ILIAS\Export\ImportHandler\File\XML\Manifest\Handler as ilImportHandlerManifestXMLFile;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\FactoryInterface as ilImportHandlerManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerCollectionInterface as ilImportHandlerManifestXMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerInterface as ilImportHandlerManifestXMLFileInterface;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ilLogger;

class Factory implements ilImportHandlerManifestFileFactoryInterface
{
    protected ilImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;
    protected ilImportStatusFactory $import_status_factory;

    public function __construct(
        ilImportHandlerFactoryInterface $import_handler,
        ilLogger $logger,
        ilImportStatusFactory $import_status_factory
    ) {
        $this->import_handler = $import_handler;
        $this->logger = $logger;
        $this->import_status_factory = $import_status_factory;
    }

    public function handler(): ilImportHandlerManifestXMLFileInterface
    {
        return new ilImportHandlerManifestXMLFile(
            $this->import_handler->file()->namespace(),
            $this->import_status_factory,
            $this->import_handler->validation()->handler(),
            $this->import_handler->parser(),
            $this->import_handler->path(),
            $this->import_handler->file()->xml(),
            $this->import_handler->file()->xsd(),
            $this->import_handler->schema()->folder()->handler()
        );
    }

    public function collection(): ilImportHandlerManifestXMLFileCollectionInterface
    {
        return new ilImportHandlerManifestXMLFileCollection(
            $this->import_status_factory
        );
    }
}
