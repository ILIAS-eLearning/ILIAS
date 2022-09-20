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
 * Unit tests for ilWebLinkItemExternal
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilWebResourceItemExternalTest extends TestCase
{
    public function testGetResolvedLink(): void
    {
        $param1 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['appendToLink'])
                       ->getMock();
        $param1->expects($this->once())
               ->method('appendToLink')
               ->with('target')
               ->willReturn('target?param1');
        $param2 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['appendToLink'])
                       ->getMock();
        $param2->expects($this->once())
               ->method('appendToLink')
               ->with('target?param1')
               ->willReturn('target?param1&param2');

        $item = new ilWebLinkItemExternal(
            0,
            1,
            'title',
            null,
            'target',
            true,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            [$param1, $param2]
        );

        $this->assertSame(
            'target?param1&param2',
            $item->getResolvedLink(true)
        );
        $this->assertSame(
            'target',
            $item->getResolvedLink(false)
        );
    }
}
