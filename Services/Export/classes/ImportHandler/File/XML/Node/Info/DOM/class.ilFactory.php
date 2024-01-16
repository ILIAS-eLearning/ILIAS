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

namespace ILIAS\Export\ImportHandler\File\XML\Node\Info\DOM;

use DOMNode;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\DOM\ilFactoryInterface as ilXMLFileDOMNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\DOM\ilHandlerInterface as ilXMLFileNodeInfoDOMNodeHandlerInterface;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\DOM\ilHandler as ilXMLFileDOMNodeInfoHandler;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\ilFactoryInterface as ilXMLFileNodeInfoFactoryInterface;

class ilFactory implements ilXMLFileDOMNodeInfoFactoryInterface
{
    protected ilXMLFileNodeInfoFactoryInterface $info;

    public function __construct(
        ilXMLFileNodeInfoFactoryInterface $info
    ) {
        $this->info = $info;
    }

    public function withDOMNode(DOMNode $node): ilXMLFileNodeInfoDOMNodeHandlerInterface
    {
        return (new ilXMLFileDOMNodeInfoHandler(
            $this->info
        ))->withDOMNode($node);
    }
}
