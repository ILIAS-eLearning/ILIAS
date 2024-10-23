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

namespace ILIAS\Export\ImportHandler\Validation;

use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\FactoryInterface as ilFileValidationFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\HandlerInterface as ilFileValidationHandlerInterface;
use ILIAS\Export\ImportHandler\I\Validation\Set\FactoryInterface as ilFileValidationSetFactoryInterface;
use ILIAS\Export\ImportHandler\Validation\Handler as ilFileValidationHandler;
use ILIAS\Export\ImportHandler\Validation\Set\Factory as ilFileValidationSetFactory;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ilLogger;

class Factory implements ilFileValidationFactoryInterface
{
    protected ilLogger $logger;
    protected ilImportHandlerFactoryInterface $import_handler;
    protected ilImportStatusFactoryInterface $import_status_factory;

    public function __construct(
        ilImportStatusFactoryInterface $import_status_factory,
        ilImportHandlerFactoryInterface $import_handler,
        ilLogger $logger,
    ) {
        $this->import_status_factory = $import_status_factory;
        $this->import_handler = $import_handler;
        $this->logger = $logger;
    }

    public function handler(): ilFileValidationHandlerInterface
    {
        return new ilFileValidationHandler(
            $this->logger,
            $this->import_handler->parser(),
            $this->import_status_factory,
            $this->import_handler->path()
        );
    }

    public function set(): ilFileValidationSetFactoryInterface
    {
        return new ilFileValidationSetFactory();
    }
}
