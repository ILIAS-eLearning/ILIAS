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

namespace ImportHandler\File\Validation;

use ilLogger;
use ImportHandler\File\Validation\ilHandler as ilFileValidationHandler;
use ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ImportHandler\I\File\Validation\ilFactoryInterface as ilFileValidationFactoryInterface;
use ImportHandler\I\File\Validation\ilHandlerInterface as ilFileValidationHandlerInterface;
use ImportHandler\I\Parser\ilHandlerInterface as ilParserHandlerInterface;
use ImportStatus\ilFactory as ilImportStatusFactory;

class ilFactory implements ilFileValidationFactoryInterface
{
    protected ilLogger $logger;
    protected ilParserHandlerInterface $parser_handler;
    protected ilFilePathFactoryInterface $path;

    public function __construct(
        ilLogger $logger,
        ilParserHandlerInterface $parser_handler,
        ilFilePathFactoryInterface $path
    ) {
        $this->logger = $logger;
        $this->parser_handler = $parser_handler;
        $this->path = $path;
    }

    public function handler(): ilFileValidationHandlerInterface
    {
        return new ilFileValidationHandler(
            $this->logger,
            $this->parser_handler,
            new ilImportStatusFactory(),
            $this->path
        );
    }
}
