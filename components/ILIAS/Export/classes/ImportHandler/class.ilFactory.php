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

namespace ImportHandler;

use ilLogger;
use ImportHandler\File\ilFactory as ilFileFactory;
use ImportHandler\I\File\ilFactoryInterface as ilFileFactoryInterface;
use ImportHandler\I\ilFactoryInterface as ilImportHandlerFactoryInterface;
use ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ImportHandler\Parser\ilFactory as ilParserFactory;

class ilFactory implements ilImportHandlerFactoryInterface
{
    protected ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();
    }

    public function parser(): ilParserFactoryInterface
    {
        return new ilParserFactory($this->logger);
    }

    public function file(): ilFileFactoryInterface
    {
        return new ilFileFactory(
            new ilParserFactory($this->logger),
            $this->logger
        );
    }
}
