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

namespace ImportHandler\Parser;

use ilLogger;
use ImportHandler\File\XML\Node\Info\ilFactory as ilXMLFileNodeInfoFactory;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\Parser\DOM\ilFactoryInterface as ilDOMParserFactoryInterface;
use ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ImportHandler\I\Parser\ilHandlerInterface as ilParserHandlerInterface;
use ImportHandler\Parser\DOM\ilFactory as ilDOMParserFactory;

class ilFactory implements ilParserFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function DOM(): ilDOMParserFactoryInterface
    {
        return new ilDOMParserFactory($this->logger);
    }
}
