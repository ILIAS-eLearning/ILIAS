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

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data\DateFormat;
use PHPUnit\Framework\TestCase;

class DateFormatTest extends TestCase
{
    public function setUp(): void
    {
        $f = new ILIAS\Data\Factory();
        $this->df = $f->dateFormat();
    }

    public function testDateFormatFactory(): void
    {
        $this->assertInstanceOf(DateFormat\DateFormat::class, $this->df->standard());
        $this->assertInstanceOf(DateFormat\DateFormat::class, $this->df->germanShort());
        $this->assertInstanceOf(DateFormat\DateFormat::class, $this->df->germanLong());
        $this->assertInstanceOf(DateFormat\DateFormat::class, $this->df->americanShort());
        $this->assertInstanceOf(DateFormat\FormatBuilder::class, $this->df->custom());
    }

    public function testDateFormatBuilderAndGetters(): void
    {
        $expect = [
            '.', ',', '-', '/', ' ', ':', 'd', 'jS', 'l', 'D', 'W', 'm', 'F', 'M', 'Y', 'y', 'h','H', 'i', 's', 'a'
        ];
        $format = $this->df->custom()
            ->dot()->comma()->dash()->slash()->space()->colon()
            ->day()->dayOrdinal()->weekday()->weekdayShort()
            ->week()->month()->monthSpelled()->monthSpelledShort()
            ->year()->twoDigitYear()
            ->hours12()->hours24()->minutes()->seconds()->meridiem()
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

    public function testDateFormatInvalidTokens(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DateFormat\DateFormat(['x', '2']);
    }

    public function testDateFormatApplyTo(): void
    {
        $dt = new DateTimeImmutable("1985-04-05");
        $format = $this->df->germanShort();
        $this->assertEquals("05.04.1985", $format->applyTo($dt));
        $this->assertEquals("05.04.1985", $dt->format((string) $format));
    }

    public function testDateFormatApplyToWithTime(): void
    {
        $dt = new DateTimeImmutable("1985-04-05 21:12:30");
        $format = $this->df->custom()
            ->day()->dot()->month()->dot()->year()
            ->space()->hours12()->colon()->minutes()->space()->meridiem()
            ->get();
        $this->assertEquals("05.04.1985 09:12 pm", $format->applyTo($dt));
        $this->assertEquals("05.04.1985 09:12 pm", $dt->format((string) $format));
        $format = $this->df->custom()
            ->day()->dot()->month()->dot()->year()
            ->space()->hours24()->colon()->minutes()->colon()->seconds()
            ->get();
        $this->assertEquals("05.04.1985 21:12:30", $format->applyTo($dt));
    }

    public function testDateFormatExpand(): void
    {
        $format = $this->df->germanShort();
        $appended = $this->df->amend($format)->dot()->dot()->get();
        $this->assertInstanceOf(DateFormat\DateFormat::class, $appended);
        $this->assertEquals(
            array_merge($format->toArray(), ['.', '.']),
            $appended->toArray()
        );
    }
}
