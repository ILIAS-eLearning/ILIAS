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

namespace ILIAS\Export\ImportHandler\File;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Export\ImportHandler\File\Namespace\Factory as FileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\XML\Factory as XMLFileFactory;
use ILIAS\Export\ImportHandler\File\XSD\Factory as XSDFileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\FactoryInterface as FileFactory;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as FileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\FactoryInterface as XMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\FactoryInterface as XSDFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\HandlerInterface as SchemaFolderInterface;
use ILIAS\Export\ImportStatus\ilFactory as ImportStatusFactory;
use ilLanguage;
use ilLogger;

class Factory implements FileFactory
{
    protected SchemaFolderInterface $schema_folder;
    protected ImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;
    protected ilLanguage $lng;
    protected ImportStatusFactory $import_status_factory;
    protected DataFactory $data_factory;

    public function __construct(
        ImportHandlerFactoryInterface $import_handler,
        ImportStatusFactory $import_status_factory,
        ilLogger $logger,
        ilLanguage $lng,
        DataFactory $data_factory,
        SchemaFolderInterface $schema_folder
    ) {
        $this->import_handler = $import_handler;
        $this->import_status_factory = $import_status_factory;
        $this->logger = $logger;
        $this->lng = $lng;
        $this->data_factory = $data_factory;
        $this->schema_folder = $schema_folder;
    }

    public function xml(): XMLFileFactoryInterface
    {
        return new XMLFileFactory(
            $this->import_handler,
            $this->import_status_factory,
            $this->logger,
            $this->lng,
            $this->data_factory,
            $this->schema_folder
        );
    }

    public function xsd(): XSDFileFactoryInterface
    {
        return new XSDFileFactory(
            $this->import_handler
        );
    }

    public function namespace(): FileNamespaceFactoryInterface
    {
        return new FileNamespaceFactory();
    }
}
