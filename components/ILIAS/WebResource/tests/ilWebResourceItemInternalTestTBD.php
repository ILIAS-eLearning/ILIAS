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
                     ->onlyMethods(['appendParameter'])
                     ->getMock();
        $item->method('appendParameter')->willReturnCallback(
            fn(string $link, string $key, string $value) => $link . '.' . $key . '.' . $value
        );
        return $item;
    }

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
                   ->with(0, 'wiki', true)
                   ->andReturn('wiki_page');
        $array_util->shouldReceive('_getStaticLink')
                   ->once()
                   ->with(0, 'git', true)
                   ->andReturn('gl_term');
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

        $item = $this->getItem('tar|13', $param1, $param2);
        $this->assertSame(
            'tar.13?param1&param2',
            $item->getResolvedLink(true)
        );
        $this->assertSame(
            'tar.13',
            $item->getResolvedLink(false)
        );

        $item = $this->getItem('wpage|14', $param1, $param2);
        $this->assertSame(
            'wiki_page.target.wiki_wpage_14',
            $item->getResolvedLink(false)
        );

        $item = $this->getItem('term|15', $param1, $param2);
        $this->assertSame(
            'gl_term.target.git_15',
            $item->getResolvedLink(false)
        );

        $item = $this->getItem('page|16', $param1, $param2);
        $this->assertSame(
            'lm_page.16',
            $item->getResolvedLink(false)
        );
    }
}
