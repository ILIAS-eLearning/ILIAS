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

namespace ILIAS\Export\ImportHandler\Parser\DOM;

use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\DOM\FactoryInterface as DOMParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\DOM\HandlerInterface as DOMParserInterface;
use ILIAS\Export\ImportHandler\Parser\DOM\Handler as DOMParser;
use ilLogger;

class Factory implements DOMParserFactoryInterface
{
    protected ImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;

    public function __construct(
        ImportHandlerFactoryInterface $import_handler,
        ilLogger $logger
    ) {
        $this->import_handler = $import_handler;
        $this->logger = $logger;
    }

    public function handler(): DOMParserInterface
    {
        return new DOMParser(
            $this->logger,
            $this->import_handler->parser()->nodeInfo()
        );
    }
}
