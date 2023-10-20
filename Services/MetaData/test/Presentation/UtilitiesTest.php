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

namespace ILIAS\MetaData\Presentation;

use PHPUnit\Framework\TestCase;
use ILIAS\Data\DateFormat\DateFormat;

class UtilitiesTest extends TestCase
{
    protected Utilities $utilities;
    protected DateFormat $format;

    protected function setUp(): void
    {
        $lng = $this->createMock(\ilLanguage::class);
        $lng->expects(self::once())->method('loadLanguageModule')->with('meta');
        $map = ['key1' => 'text', 'key2' => 'text with %s'];
        $lng->method('txt')->willReturnCallback(function ($arg) use ($map) {
            return $map[$arg] ?? '';
        });
        $lng->method('exists')->willReturnCallback(function ($arg) use ($map) {
            return key_exists($arg, $map);
        });

        $this->format = $this->createMock(DateFormat::class);
        $user = $this->createMock(\ilObjUser::class);
        $user->method('getDateFormat')->willReturn($this->format);

        $this->utilities = new Utilities($lng, $user);
    }

    public function testGetUserDateFormat(): void
    {
        $this->assertEquals(
            $this->format,
            $this->utilities->getUserDateFormat()
        );
    }

    public function testTxt(): void
    {
        $this->assertSame(
            'text',
            $this->utilities->txt('key1')
        );
    }

    public function testTxtFill(): void
    {
        $this->assertSame(
            'text',
            $this->utilities->txtFill('key1', 'more text')
        );
        $this->assertSame(
            'text with more text',
            $this->utilities->txtFill('key2', 'more text')
        );
        $this->assertSame(
            'wrong key first, second',
            $this->utilities->txtFill('wrong key', 'first', 'second')
        );
    }
}
