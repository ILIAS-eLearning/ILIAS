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

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo\DOM;

use DOMNode;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\DOM\FactoryInterface as ilImportHandlerParserDOMNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\DOM\HandlerInterface as ilImportHandlerParserNodeInfoDOMNodeInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\FactoryInterface as ilImportHandlerParserNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\DOM\Handler as ilImportHandlerParserDOMNodeInfo;

class Factory implements ilImportHandlerParserDOMNodeInfoFactoryInterface
{
    protected ilImportHandlerParserNodeInfoFactoryInterface $info;

    public function __construct(
        ilImportHandlerParserNodeInfoFactoryInterface $info
    ) {
        $this->info = $info;
    }

    public function withDOMNode(DOMNode $node): ilImportHandlerParserNodeInfoDOMNodeInterface
    {
        return (new ilImportHandlerParserDOMNodeInfo(
            $this->info
        ))->withDOMNode($node);
    }
}
