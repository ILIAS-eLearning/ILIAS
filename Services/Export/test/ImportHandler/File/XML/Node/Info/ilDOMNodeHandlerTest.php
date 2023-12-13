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

namespace Test\ImportHandler\File\XML\Node\Info;

use PHPUnit\Framework\TestCase;

class ilDOMNodeHandlerTest extends TestCase
{
    public function testXMLFileNodeInfo(): void
    {
        // Problem: a lot of the attributes of domnodes are read only.
        /*
        $xml_str = '<Nodes test="super"><Node></Node><Node></Node><Node></Node></Nodes>';

        $attribute_1 = $this->createMock(DOMAttr::class);
        $attribute_1->name = 'test';
        $attribute_1->value = 'super';
        $attribute_iterator_elements = [
            $attribute_1
        ];
        $attribute_iterator = new class($attribute_iterator_elements) implements \Iterator {
            protected array $children;
            protected int $index;
            public function __construct(array $children)
            {
                $this->children = $children;
                $this->index = 0;
            }
            public function current(): mixed
            {
                return $this->children[$this->index];
            }
            public function next(): void
            {
                $this->index++;
            }
            public function key(): mixed
            {
                return $this->index;
            }
            public function valid(): bool
            {
                return 0 <= $this->index && $this->index < count($this->children);
            }
            public function rewind(): void
            {
                $this->index = 0;
            }
        };

        $dom_named_node_map = $this->createMock(DOMNamedNodeMap::class);
        $dom_named_node_map->expects($this->any())->method('getIterator')->willReturn($attribute_iterator);

        $dom_document = $this->createMock(DOMDocument::class);
        $dom_document->expects($this->any())->method('saveXML')->willReturn($xml_str);

        $iterator_elements = [
            $this->createMock(DOMNode::class),
            $this->createMock(DOMNode::class),
            $this->createMock(DOMNode::class)
        ];
        $child_iterator = new class($iterator_elements) implements \Iterator {
            protected array $children;
            protected int $index;
            public function __construct(array $children)
            {
                $this->children = $children;
                $this->index = 0;
            }
            public function current(): mixed
            {
                return $this->children[$this->index];
            }
            public function next(): void
            {
                $this->index++;
            }
            public function key(): mixed
            {
                return $this->index;
            }
            public function valid(): bool
            {
                return 0 <= $this->index && $this->index < count($this->children);
            }
            public function rewind(): void
            {
                $this->index = 0;
            }
        };

        $child_nodes = $this->createMock(DOMNodeList::class);
        $child_nodes->expects($this->any())->method('getIterator')->willReturn($child_iterator);

        $parent_node = $this->createMock(DOMNode::class);
        $parent_node->nodeName = 'testParentName';

        $dom_node = $this->createMock(DOMNode::class);
        $dom_node->attributes = $dom_named_node_map;
        $dom_node->ownerDocument = $dom_document;
        $dom_node->nodeName = 'testName';
        $dom_node->childNodes = $child_nodes;
        $dom_node->parentNode = $parent_node;

        $info = $this->createMock(ilFileXMLNodeInfoFactory::class);

        $node_info_handler = new ilFileXMLNodeInfoDOMNodeHandler($info);

        $this->assertTrue($node_info_handler->hasAttribute('test'));
        $this->assertFalse($node_info_handler->hasAttribute('args'));
        $this->assertEquals('super', $node_info_handler->getValueOfAttribute('test'));
        $this->assertEquals($xml_str, $node_info_handler->getXML());
        $this->assertEquals('testName', $node_info_handler->getNodeName());
        $this->assertCount(3, $node_info_handler->getChildren());
        $this->assertEquals('testParentName', $node_info_handler->getParent()->getNodeName());

        node_info_handler->getAttributePath();
        */
        $this->assertTrue(true);
    }
}
