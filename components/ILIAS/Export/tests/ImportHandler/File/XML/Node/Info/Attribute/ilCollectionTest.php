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

namespace Test\ImportHandler\File\XML\Node\Info\Attribute;

use ilLogger;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\Attribute\ilCollection as ilXMLFileNodeInfoAttributeCollection;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\Attribute\ilPair as ilXMLFileNodeInfoAttributePair;
use ILIAS\Export\ImportHandler\File\XML\Node\Info\DOM\ilHandler as ilXMLFileNodeInfoDOMNodeHandler;
use PHPUnit\Framework\TestCase;

class ilCollectionTest extends TestCase
{
    public function testNodeInfoAttributeCollection(): void
    {
        $logger = $this->createMock(ilLogger::class);
        $node_info = $this->createMock(ilXMLFileNodeInfoDOMNodeHandler::class);
        $node_info->expects($this->any())->method('getValueOfAttribute')->will($this->returnValueMap([
            ['key1', 'val1'],
            ['key2', 'val2'],
            ['key3', 'val3'],
        ]));
        $node_info->expects($this->any())->method('hasAttribute')->will($this->returnValueMap([
            ['key1', true],
            ['key2', true],
            ['key3', true],
            ['key4', false]
        ]));
        $pair1 = $this->createMock(ilXMLFileNodeInfoAttributePair::class);
        $pair1->expects($this->any())->method('getKey')->willReturn('key1');
        $pair1->expects($this->any())->method('getValue')->willReturn('val1');
        $pair2 = $this->createMock(ilXMLFileNodeInfoAttributePair::class);
        $pair2->expects($this->any())->method('getKey')->willReturn('key2');
        $pair2->expects($this->any())->method('getValue')->willReturn('val2');
        $pair3 = $this->createMock(ilXMLFileNodeInfoAttributePair::class);
        $pair3->expects($this->any())->method('getKey')->willReturn('key3');
        $pair3->expects($this->any())->method('getValue')->willReturn('val3');
        $pair4 = $this->createMock(ilXMLFileNodeInfoAttributePair::class);
        $pair4->expects($this->any())->method('getKey')->willReturn('key4');
        $pair4->expects($this->any())->method('getValue')->willReturn('val4');

        $collection = (new ilXMLFileNodeInfoAttributeCollection($logger))
            ->withElement($pair1)
            ->withElement($pair2)
            ->withElement($pair3);
        $collection2 = $collection
            ->withElement($pair4);

        $this->assertTrue($collection->matches($node_info));
        $this->assertFalse($collection2->matches($node_info));
    }
}
