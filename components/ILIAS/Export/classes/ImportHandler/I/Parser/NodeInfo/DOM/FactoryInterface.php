<?php

namespace ILIAS\Export\ImportHandler\I\Parser\NodeInfo\DOM;

use DOMNode;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\DOM\HandlerInterface as ilXMLFileNodeInfoDOMNodeHandlerInterface;

interface FactoryInterface
{
    public function withDOMNode(DOMNode $node): ilXMLFileNodeInfoDOMNodeHandlerInterface;
}
