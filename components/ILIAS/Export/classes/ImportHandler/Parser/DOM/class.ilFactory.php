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

namespace ImportHandler\Parser\DOM;

use ilLogger;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\Parser\DOM\ilFactoryInterface as ilDOMParserFactoryInterface;
use ImportHandler\I\Parser\DOM\ilHandlerInterface as ilDOMParserHandlerInterface;
use ImportHandler\Parser\DOM\ilHandler as ilDOMParser;
use ImportHandler\Parser\DOM\ilHandler as ilDOMParserHandler;
use ImportHandler\File\XML\Node\Info\ilFactory as ilXMLFileNodeInfoFactory;

class ilFactory implements ilDOMParserFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function withFileHandler(ilXMLFileHandlerInterface $file_handler): ilDOMParserHandlerInterface
    {
        return (new ilDOMParser(
            $this->logger,
            new ilXMLFileNodeInfoFactory($this->logger)
        ))->withFileHandler($file_handler);
    }
}
