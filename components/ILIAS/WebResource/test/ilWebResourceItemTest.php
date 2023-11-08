<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ilWebLinkItem
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilWebResourceItemTest extends TestCase
{
    public function testToXML(): void
    {
        $writer = $this->getMockBuilder(ilXmlWriter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['xmlStartTag', 'xmlElement', 'xmlEndTag'])
                       ->getMock();
        $writer->expects($this->once())
               ->method('xmlStartTag')
               ->with('WebLink', [
                   'id' => 13,
                   'active' => 1,
                   'position' => 7,
                   'internal' => 0
               ]);
        $writer->expects($this->exactly(3))
               ->method('xmlElement')
               ->withConsecutive(
                   ['Title', [], 'title'],
                   ['Description', [], 'description'],
                   ['Target', [], 'target'],
               );
        $writer->expects($this->once())
               ->method('xmlEndTag')
               ->with('WebLink');

        $param1 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['toXML'])
                       ->getMock();
        $param1->expects($this->once())
               ->method('toXML')
               ->with($writer);
        $param2 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['toXML'])
                       ->getMock();
        $param2->expects($this->once())
               ->method('toXML')
               ->with($writer);

        $item_stub = $this->getMockForAbstractClass(
            ilWebLinkItem::class,
            [
                1, 13, 'title', 'description', 'target',
                true, new DateTimeImmutable(), new DateTimeImmutable(),
                [$param1, $param2]
            ]
        );
        $item_stub->expects($this->once())
                  ->method('isInternal')
                  ->willReturn(false);
        $item_stub->toXML($writer, 7);
    }
}
