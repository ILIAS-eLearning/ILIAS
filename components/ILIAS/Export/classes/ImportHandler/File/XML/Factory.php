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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Export\ImportHandler\File\XML\Collection as XMLFileCollection;
use ILIAS\Export\ImportHandler\File\XML\Export\Factory as XMLExportFileFactory;
use ILIAS\Export\ImportHandler\File\XML\Handler as XMLFile;
use ILIAS\Export\ImportHandler\File\XML\Manifest\Factory as ManifestFileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\CollectionInterface as XMLFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\FactoryInterface as XMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\FactoryInterface as XMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\FactoryInterface as ManifestFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as SchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\HandlerInterface as SchemaFolderInterface;
use ILIAS\Export\ImportHandler\Schema\Factory as SchemaFactory;
use ILIAS\Export\ImportStatus\ilFactory as ImportStatusFactory;
use ilLanguage;
use ilLogger;

class Factory implements XMLFileFactoryInterface
{
    protected ImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ImportStatusFactory $import_status_factory;
    protected DataFactory $data_factory;
    protected SchemaFolderInterface $schema_folder;

    public function __construct(
        ImportHandlerFactoryInterface $import_handler,
        ImportStatusFactory $import_status_factory,
        ilLogger $logger,
        ilLanguage $lng,
        DataFactory $data_factory,
        SchemaFolderInterface $schema_folder
    ) {
        $this->schema_folder = $schema_folder;
        $this->import_handler = $import_handler;
        $this->logger = $logger;
        $this->lng = $lng;
        $this->data_factory = $data_factory;
        $this->import_status_factory = $import_status_factory;
    }

    public function handler(): XMLFileInterface
    {
        return new XMLFile(
            $this->import_handler->file()->namespace(),
            $this->import_status_factory
        );
    }

    public function collection(): XMLFileCollectionInterface
    {
        return new XMLFileCollection();
    }

    public function manifest(): ManifestFileFactoryInterface
    {
        return new ManifestFileFactory(
            $this->import_handler,
            $this->logger,
            $this->import_status_factory,
            $this->schema_folder
        );
    }

    public function export(): XMLExportFileFactoryInterface
    {
        return new XMLExportFileFactory(
            $this->import_handler,
            $this->logger,
            $this->lng
        );
    }

    public function schema(): SchemaFactoryInterface
    {
        return new SchemaFactory(
            $this->import_handler,
            $this->data_factory,
            $this->logger
        );
    }
}
