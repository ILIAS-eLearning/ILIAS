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

use ILIAS\Export\ImportHandler\File\XML\Export\Collection as XMLExportFileHandlerCollection;
use ILIAS\Export\ImportHandler\File\XML\Export\Component\Factory as ComponentXMLExportFileFactory;
use ILIAS\Export\ImportHandler\File\XML\Export\DataSet\Factory as DataSetXMLExportFileFactory;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\CollectionInterface as XMLExportFileCollectionInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\Component\FactoryInterface as ComponentXMLExportFileHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\FactoryInterface as DataSetXMLExportFileHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\FactoryInterface as XMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\HandlerInterface as XMLExportFileHandlerInterface;
use ilLanguage;
use ilLogger;
use SplFileInfo;

class Factory implements XMLExportFileFactoryInterface
{
    protected ImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;
    protected ilLanguage $lng;

    public function __construct(
        ImportHandlerFactoryInterface $import_handler,
        ilLogger $logger,
        ilLanguage $lng
    ) {
        $this->logger = $logger;
        $this->lng = $lng;
        $this->import_handler = $import_handler;
    }

    public function withFileInfo(SplFileInfo $file_info): XMLExportFileHandlerInterface
    {
        $comp_handler = $this->component()->handler()->withFileInfo($file_info);
        $dataset_handler = $this->dataSet()->handler()->withFileInfo($file_info);
        if ($dataset_handler->hasComponentRootNode()) {
            return $dataset_handler;
        }
        return $comp_handler;
    }

    public function collection(): XMLExportFileCollectionInterface
    {
        return new XMLExportFileHandlerCollection();
    }

    public function component(): ComponentXMLExportFileHandlerFactoryInterface
    {
        return new ComponentXMLExportFileFactory(
            $this->import_handler,
            $this->logger,
            $this->lng
        );
    }

    public function dataSet(): DataSetXMLExportFileHandlerFactoryInterface
    {
        return new DataSetXMLExportFileFactory(
            $this->import_handler,
            $this->logger,
            $this->lng
        );
    }
}
