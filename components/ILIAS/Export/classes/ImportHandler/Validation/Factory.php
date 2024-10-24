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

use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\FactoryInterface as FileValidationFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\HandlerInterface as FileValidationHandlerInterface;
use ILIAS\Export\ImportHandler\I\Validation\Set\FactoryInterface as FileValidationSetFactoryInterface;
use ILIAS\Export\ImportHandler\Validation\Handler as FileValidationHandler;
use ILIAS\Export\ImportHandler\Validation\Set\Factory as FileValidationSetFactory;
use ILIAS\Export\ImportStatus\I\ilFactoryInterface as ImportStatusFactoryInterface;
use ilLogger;

class Factory implements FileValidationFactoryInterface
{
    protected ilLogger $logger;
    protected ImportHandlerFactoryInterface $import_handler;
    protected ImportStatusFactoryInterface $import_status_factory;

    public function __construct(
        ImportStatusFactoryInterface $import_status_factory,
        ImportHandlerFactoryInterface $import_handler,
        ilLogger $logger,
    ) {
        $this->import_status_factory = $import_status_factory;
        $this->import_handler = $import_handler;
        $this->logger = $logger;
    }

    public function handler(): FileValidationHandlerInterface
    {
        return new FileValidationHandler(
            $this->logger,
            $this->import_handler->parser(),
            $this->import_status_factory,
            $this->import_handler->path()
        );
    }

    public function set(): FileValidationSetFactoryInterface
    {
        return new FileValidationSetFactory();
    }
}
