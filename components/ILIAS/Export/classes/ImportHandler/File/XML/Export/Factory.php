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

namespace ILIAS\Export\ImportHandler\File\XML\Export;

use ILIAS\Export\ImportHandler\File\XML\Export\Collection as ilXMLExportFileHandlerCollection;
use ILIAS\Export\ImportHandler\File\XML\Export\Component\Factory as ilComponentXMLExportFileFactory;
use ILIAS\Export\ImportHandler\File\XML\Export\DataSet\Factory as ilDataSetXMLExportFileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\CollectionInterface as ilXMLExportFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\FactoryInterface as ilComponentXMLExportFileHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\FactoryInterface as ilDataSetXMLExportFileHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\FactoryInterface as ilXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\HandlerInterface as ilXMLExportFileHandlerInterface;
use ilLanguage;
use ilLogger;
use SplFileInfo;

class Factory implements ilXMLExportFileFactoryInterface
{
    protected ilImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;
    protected ilLanguage $lng;

    public function __construct(
        ilImportHandlerFactoryInterface $import_handler,
        ilLogger $logger,
        ilLanguage $lng
    ) {
        $this->logger = $logger;
        $this->lng = $lng;
        $this->import_handler = $import_handler;
    }

    public function withFileInfo(SplFileInfo $file_info): ilXMLExportFileHandlerInterface
    {
        $comp_handler = $this->component()->handler()->withFileInfo($file_info);
        $dataset_handler = $this->dataSet()->handler()->withFileInfo($file_info);
        if ($dataset_handler->hasComponentRootNode()) {
            return $dataset_handler;
        }
        return $comp_handler;
    }

    public function collection(): ilXMLExportFileCollectionInterface
    {
        return new ilXMLExportFileHandlerCollection();
    }

    public function component(): ilComponentXMLExportFileHandlerFactoryInterface
    {
        return new ilComponentXMLExportFileFactory(
            $this->import_handler,
            $this->logger,
            $this->lng
        );
    }

    public function dataSet(): ilDataSetXMLExportFileHandlerFactoryInterface
    {
        return new ilDataSetXMLExportFileFactory(
            $this->import_handler,
            $this->logger,
            $this->lng
        );
    }
}
