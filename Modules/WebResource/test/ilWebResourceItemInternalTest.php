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
 * Unit tests for ilWebLinkItemInternal
 * @author  Tim Schmitz <schmitz@leifos.com>
 */
class ilWebResourceItemInternalTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetResolvedLink(): void
    {
        $array_util = Mockery::mock('alias:' . ilLink::class);
        $array_util->shouldReceive('_getStaticLink')
                   ->twice()
                   ->with(13, 'tar')
                   ->andReturn('tar.13');
        $array_util->shouldReceive('_getStaticLink')
                   ->once()
                   ->with(0, 'wiki', true, '&target=wiki_wpage_14')
                   ->andReturn('wiki_page.14');
        $array_util->shouldReceive('_getStaticLink')
                   ->once()
                   ->with(0, 'git', true, '&target=git_15')
                   ->andReturn('gl_term.15');
        $array_util->shouldReceive('_getStaticLink')
                   ->once()
                   ->with(16, 'pg')
                   ->andReturn('lm_page.16');

        $param1 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['appendToLink'])
                       ->getMock();
        $param1->expects($this->once())
               ->method('appendToLink')
               ->with('tar.13')
               ->willReturn('tar.13?param1');
        $param2 = $this->getMockBuilder(ilWebLinkParameter::class)
                       ->disableOriginalConstructor()
                       ->onlyMethods(['appendToLink'])
                       ->getMock();
        $param2->expects($this->once())
               ->method('appendToLink')
               ->with('tar.13?param1')
               ->willReturn('tar.13?param1&param2');

        $item = new ilWebLinkItemInternal(
            0,
            1,
            'title',
            null,
            'tar|13',
            true,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            [$param1, $param2]
        );

        $this->assertSame(
            'tar.13?param1&param2',
            $item->getResolvedLink(true)
        );
        $this->assertSame(
            'tar.13',
            $item->getResolvedLink(false)
        );

        $item = new ilWebLinkItemInternal(
            0,
            1,
            'title',
            null,
            'wpage|14',
            true,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            [$param1, $param2]
        );

        $this->assertSame(
            'wiki_page.14',
            $item->getResolvedLink(false)
        );

        $item = new ilWebLinkItemInternal(
            0,
            1,
            'title',
            null,
            'term|15',
            true,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            [$param1, $param2]
        );

        $this->assertSame(
            'gl_term.15',
            $item->getResolvedLink(false)
        );

        $item = new ilWebLinkItemInternal(
            0,
            1,
            'title',
            null,
            'page|16',
            true,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            [$param1, $param2]
        );

        $this->assertSame(
            'lm_page.16',
            $item->getResolvedLink(false)
        );
    }
}
