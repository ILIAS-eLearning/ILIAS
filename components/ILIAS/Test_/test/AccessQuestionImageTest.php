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

namespace ILIAS\Modules\Test\test;

use PHPUnit\Framework\TestCase;
use ILIAS\Modules\Test\AccessQuestionImage;
use ILIAS\Modules\Test\Readable;

class AccessQuestionImageTest extends TestCase
{
    public function testConstruct(): void
    {
        $readable = $this->getMockBuilder(Readable::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(AccessQuestionImage::class, new AccessQuestionImage($readable));
    }

    /**
     * @dataProvider invalidPaths
     */
    public function testIsPermittedWithInvalidPath(string $path): void
    {
        $readable = $this->getMockBuilder(Readable::class)->disableOriginalConstructor()->getMock();

        $instance = new AccessQuestionImage($readable);

        $this->assertFalse($instance->isPermitted($path)->isOk());
    }

    public function invalidPaths(): array
    {
        return [
            ['foo'],
            ['/assessment/12/images/foo.png'],
            ['/assessment/12/34/images/foo/bar.png'],
            ['/assessment/12/ab/images/foo.png'],
            ['/assessment/ab/12/images/foo.png'],
            ['assessment/12/34/images/foo.png'],
        ];
    }

    /**
     * @dataProvider isPermittedProvider
     */
    public function testIsPermittedWithValidPath(bool $is_readable): void
    {
        $readable = $this->getMockBuilder(Readable::class)->disableOriginalConstructor()->getMock();

        $readable->method('objectId')->with(6709)->willReturn($is_readable);

        $instance = new AccessQuestionImage($readable);
        $result = $instance->isPermitted('/assessment/6709/389/images/foo.png');
        $this->assertTrue($result->isOk());
        $this->assertSame($is_readable, $result->value());
    }

    public function isPermittedProvider(): array
    {
        return [
            'With readable object path.' => [true],
            'Without readable object path.' => [false],
        ];
    }
}
