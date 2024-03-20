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

use ILIAS\Export\ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ILIAS\Export\ImportHandler\File\Validation\ilHandler as ilFileValidationHandler;
use ILIAS\Export\ImportHandler\File\Validation\Set\ilFactory as ilFileValidationSetFactory;
use ILIAS\Export\ImportHandler\I\File\Validation\ilFactoryInterface as ilFileValidationFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\ilHandlerInterface as ilFileValidationHandlerInterface;
use ILIAS\Export\ImportHandler\I\File\Validation\Set\ilFactoryInterface as ilFileValidationSetFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\ilFactory as ilParserFactory;
use ILIAS\Export\ImportStatus\ilFactory as ilImportStatusFactory;
use ilLogger;

class ilFactory implements ilFileValidationFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger,
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilFileValidationHandlerInterface
    {
        return new ilFileValidationHandler(
            $this->logger,
            new ilParserFactory($this->logger),
            new ilImportStatusFactory(),
            new ilFilePathFactory($this->logger)
        );
    }

    public function set(): ilFileValidationSetFactoryInterface
    {
        return new ilFileValidationSetFactory();
    }
}
