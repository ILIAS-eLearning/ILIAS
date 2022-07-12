<?php declare(strict_types=1);

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

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data\DateFormat;
use PHPUnit\Framework\TestCase;

class DateFormatTest extends TestCase
{
    public function setUp() : void
    {
        $f = new ILIAS\Data\Factory();
        $this->df = $f->dateFormat();
    }

    public function testDateFormatFactory() : void
    {
        $this->assertInstanceOf(DateFormat\DateFormat::class, $this->df->standard());
        $this->assertInstanceOf(DateFormat\DateFormat::class, $this->df->germanShort());
        $this->assertInstanceOf(DateFormat\DateFormat::class, $this->df->germanLong());
        $this->assertInstanceOf(DateFormat\FormatBuilder::class, $this->df->custom());
    }

    public function testDateFormatBuilderAndGetters() : void
    {
        $expect = [
            '.', ',', '-', '/', ' ', 'd', 'jS', 'l', 'D', 'W', 'm', 'F', 'M', 'Y', 'y'
        ];
        $format = $this->df->custom()
            ->dot()->comma()->dash()->slash()->space()
            ->day()->dayOrdinal()->weekday()->weekdayShort()
            ->week()->month()->monthSpelled()->monthSpelledShort()
            ->year()->twoDigitYear()
            ->get();

        $this->assertEquals(
            $expect,
            $format->toArray()
        );

        $this->assertEquals(
            implode('', $expect),
            $format->toString()
        );

        $this->assertEquals(
            $format->toString(),
            (string) $format
        );
    }

    public function testInvalidTokens() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new DateFormat\DateFormat(['x', '2']);
    }

    public function test_applyTo() : void
    {
        $dt = new DateTimeImmutable("1985-04-05");
        $format = $this->df->germanShort();
        $this->assertEquals("05.04.1985", $format->applyTo($dt));
        $this->assertEquals("05.04.1985", $dt->format((string) $format));
    }
}
