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

namespace ILIAS\Export\ImportHandler\File\Validation;

use ilLogger;
use ILIAS\Export\ImportHandler\File\Validation\ilHandler as ilFileValidationHandler;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\ilFactoryInterface as ilFileValidationFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\ilHandlerInterface as ilFileValidationHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilFactoryInterface as ilFileValidationSetFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ILIAS\Export\ImportHandler\File\Validation\Set\ilFactory as ilFileValidationSetFactory;

class ilFactory implements ilFileValidationFactoryInterface
{
    protected ilLogger $logger;
    protected ilParserFactoryInterface $parser;
    protected ilFilePathFactoryInterface $path;

    public function __construct(
        ilLogger $logger,
        ilParserFactoryInterface $parser,
        ilFilePathFactoryInterface $path
    ) {
        $this->logger = $logger;
        $this->parser = $parser;
        $this->path = $path;
    }

    public function handler(): ilFileValidationHandlerInterface
    {
        return new ilFileValidationHandler(
            $this->logger,
            $this->parser,
            new ilImportStatusFactory(),
            $this->path
        );
    }

    public function set(): ilFileValidationSetFactoryInterface
    {
        return new ilFileValidationSetFactory();
    }
}
