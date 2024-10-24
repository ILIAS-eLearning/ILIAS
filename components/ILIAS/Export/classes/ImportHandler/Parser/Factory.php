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

namespace ILIAS\Export\ImportHandler\Parser;

use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\DOM\FactoryInterface as DOMParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\FactoryInterface as ParserNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\DOM\Factory as DOMParserFactory;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Factory as ParserNodeInfoFactory;
use ilLogger;

class Factory implements ParserFactoryInterface
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

    public function DOM(): DOMParserFactoryInterface
    {
        return new DOMParserFactory(
            $this->import_handler,
            $this->logger
        );
    }

    public function nodeInfo(): ParserNodeInfoFactoryInterface
    {
        return new ParserNodeInfoFactory(
            $this->import_handler,
            $this->logger
        );
    }
}
