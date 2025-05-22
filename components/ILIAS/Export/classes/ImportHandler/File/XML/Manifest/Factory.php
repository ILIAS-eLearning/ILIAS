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

use ILIAS\Export\ImportHandler\File\XML\Manifest\Collection as ManifestXMLFileCollection;
use ILIAS\Export\ImportHandler\File\XML\Manifest\Handler as ManifestXMLFile;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\FactoryInterface as ManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerCollectionInterface as ManifestXMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerInterface as ManifestXMLFileInterface;
use ILIAS\Export\ImportStatus\ilFactory as ImportStatusFactory;
use ILIAS\Export\ImportHandler\I\SchemaFolder\HandlerInterface as SchemaFolderInterface;
use ilLogger;

class Factory implements ManifestFileFactoryInterface
{
    protected ImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;
    protected ImportStatusFactory $import_status_factory;
    protected SchemaFolderInterface $schema_folder;

    public function __construct(
        ImportHandlerFactoryInterface $import_handler,
        ilLogger $logger,
        ImportStatusFactory $import_status_factory,
        SchemaFolderInterface $schema_folder
    ) {
        $this->import_handler = $import_handler;
        $this->logger = $logger;
        $this->import_status_factory = $import_status_factory;
        $this->schema_folder = $schema_folder;
    }

    public function handler(): ManifestXMLFileInterface
    {
        return new ManifestXMLFile(
            $this->import_handler->file()->namespace(),
            $this->import_status_factory,
            $this->import_handler->validation()->handler(),
            $this->import_handler->parser(),
            $this->import_handler->path(),
            $this->import_handler->file()->xml(),
            $this->import_handler->file()->xsd(),
            $this->schema_folder
        );
    }

    public function collection(): ManifestXMLFileCollectionInterface
    {
        return new ManifestXMLFileCollection(
            $this->import_status_factory
        );
    }
}
