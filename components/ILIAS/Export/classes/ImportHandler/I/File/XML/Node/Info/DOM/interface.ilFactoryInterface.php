<?php

namespace ImportHandler\I\File\XML\Node\Info\DOM;

use DOMNode;
use ImportHandler\I\File\XML\Node\Info\DOM\ilHandlerInterface as ilXMLFileNodeInfoDOMNodeHandlerInterface;

interface ilFactoryInterface
{
    public function withDOMNode(DOMNode $node): ilXMLFileNodeInfoDOMNodeHandlerInterface;
}
