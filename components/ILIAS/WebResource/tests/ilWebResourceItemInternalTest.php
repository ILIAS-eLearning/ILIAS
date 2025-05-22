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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ilWebLinkItemInternal
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilWebResourceItemInternalTest extends TestCase
{
    protected function getItem(string $target, ilWebLinkParameter ...$parameters): ilWebLinkItemInternal
    {
        $item = $this->getMockBuilder(ilWebLinkItemInternal::class)
                     ->setConstructorArgs([
                         0,
                         1,
                         'title',
                         null,
                         $target,
                         true,
                         new DateTimeImmutable(),
                         new DateTimeImmutable(),
                         $parameters
                     ])
                     ->onlyMethods(['appendParameter', 'getStaticLink'])
                     ->getMock();
        $item->method('appendParameter')->willReturnCallback(
            fn(string $link, string $key, string $value) => $link . '.' . $key . '.' . $value
        );
        $item->method('getStaticLink')->willReturnCallback(
            fn(int $ref_id, string $type) => $type . ':' . $ref_id
        );
        return $item;
    }

    public function testGetResolvedLink(): void
    {
        $param1 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['appendToLink'])
                       ->getMock();
        $param1->expects($this->once())
               ->method('appendToLink')
               ->with('tar:13')
               ->willReturn('tar:13?param1');
        $param2 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['appendToLink'])
                       ->getMock();
        $param2->expects($this->once())
               ->method('appendToLink')
               ->with('tar:13?param1')
               ->willReturn('tar:13?param1&param2');

        $item = $this->getItem('tar|13', $param1, $param2);
        $this->assertSame(
            'tar:13?param1&param2',
            $item->getResolvedLink(true)
        );
        $this->assertSame(
            'tar:13',
            $item->getResolvedLink(false)
        );

        $item = $this->getItem('wpage|14', $param1, $param2);
        $this->assertSame(
            'wiki:0.target.wiki_wpage_14',
            $item->getResolvedLink(false)
        );

        $item = $this->getItem('term|15', $param1, $param2);
        $this->assertSame(
            'git:0.target.git_15',
            $item->getResolvedLink(false)
        );

        $item = $this->getItem('page|16', $param1, $param2);
        $this->assertSame(
            'pg:16',
            $item->getResolvedLink(false)
        );
    }
}
