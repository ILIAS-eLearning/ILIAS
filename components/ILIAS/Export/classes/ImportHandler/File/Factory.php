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

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Export\ImportHandler\File\Namespace\Factory as ilFileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\XML\Factory as ilXMLFileFactory;
use ILIAS\Export\ImportHandler\File\XSD\Factory as ilXSDFileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\FactoryInterface as ilFileFactory;
use ILIAS\Export\ImportHandler\I\File\Namespace\FactoryInterface as ilFileNamespaceFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\FactoryInterface as ilXMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\FactoryInterface as ilXSDFileFactoryInterface;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ilLanguage;
use ilLogger;

class Factory implements ilFileFactory
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
        $this->import_status_factory = $import_status_factory;
        $this->logger = $logger;
        $this->lng = $lng;
        $this->data_factory = $data_factory;
    }

    public function xml(): ilXMLFileFactoryInterface
    {
        return new ilXMLFileFactory(
            $this->import_handler,
            $this->import_status_factory,
            $this->logger,
            $this->lng,
            $this->data_factory
        );
    }

    public function xsd(): ilXSDFileFactoryInterface
    {
        return new ilXSDFileFactory();
    }

    public function namespace(): ilFileNamespaceFactoryInterface
    {
        return new ilFileNamespaceFactory();
    }
}
