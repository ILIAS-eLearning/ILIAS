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

namespace ILIAS\Export\ImportHandler\File\XML\Export\DataSet;

use ILIAS\Export\ImportHandler\File\XML\Export\DataSet\Handler as DatasetXMLExportFileHandler;
use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\FactoryInterface as DataSetXMLExportFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Export\DataSet\HandlerInterface as DatasetXMLExportFileHandlerInterface;
use ILIAS\Export\ImportStatus\ilFactory as ImportStatusFactory;
use ilLanguage;
use ilLogger;

class Factory implements DataSetXMLExportFileFactoryInterface
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

    public function handler(): DatasetXMLExportFileHandlerInterface
    {
        return new DatasetXMLExportFileHandler(
            $this->import_handler->file()->namespace(),
            new ImportStatusFactory(),
            $this->import_handler->schema(),
            $this->import_handler->parser(),
            $this->import_handler->path(),
            $this->logger,
            $this->import_handler->parser()->nodeInfo()->attribute(),
            $this->import_handler->validation()->set(),
            $this->lng
        );
    }
}
