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

namespace ILIAS\Export\Test\ImportHandler\Parser\NodeInfo\Attribute;

use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Collection as ilXMLFileNodeInfoAttributeCollection;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Handler as ilXMLFileNodeInfoAttributePair;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\DOM\Handler as ilXMLFileNodeInfoDOMNodeHandler;
use ilLogger;
use PHPUnit\Framework\TestCase;

class ilCollectionTest extends TestCase
{
    public function testNodeInfoAttributeCollection(): void
    {
        $logger = $this->createStub(ilLogger::class);
        $node_info = $this->createStub(ilXMLFileNodeInfoDOMNodeHandler::class);
        $node_info->method('getValueOfAttribute')->willReturnMap([
            ['key1', 'val1'],
            ['key2', 'val2'],
            ['key3', 'val3'],
        ]);
        $node_info->method('hasAttribute')->willReturnMap([
            ['key1', true],
            ['key2', true],
            ['key3', true],
            ['key4', false]
        ]);
        $pair1 = $this->createStub(ilXMLFileNodeInfoAttributePair::class);
        $pair1->method('getKey')->willReturn('key1');
        $pair1->method('getValue')->willReturn('val1');
        $pair2 = $this->createStub(ilXMLFileNodeInfoAttributePair::class);
        $pair2->method('getKey')->willReturn('key2');
        $pair2->method('getValue')->willReturn('val2');
        $pair3 = $this->createStub(ilXMLFileNodeInfoAttributePair::class);
        $pair3->method('getKey')->willReturn('key3');
        $pair3->method('getValue')->willReturn('val3');
        $pair4 = $this->createStub(ilXMLFileNodeInfoAttributePair::class);
        $pair4->method('getKey')->willReturn('key4');
        $pair4->method('getValue')->willReturn('val4');

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
