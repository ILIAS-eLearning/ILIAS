<?php

namespace ILIAS\Export\ImportHandler\I\File\XML\Node\Info\DOM;

use DOMNode;
use ILIAS\Export\ImportHandler\I\File\XML\Node\Info\DOM\ilHandlerInterface as ilXMLFileNodeInfoDOMNodeHandlerInterface;

interface ilFactoryInterface
{
    public function withDOMNode(DOMNode $node): ilXMLFileNodeInfoDOMNodeHandlerInterface;
}
