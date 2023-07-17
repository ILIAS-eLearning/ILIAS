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

namespace ILIAS\MetaData\DataHelper;

use PHPUnit\Framework\TestCase;

class DataHelperTest extends TestCase
{
    protected const CORRECT_DURS = [
        'P12Y4M2DT56H900001M0S',
        'P4MT56H0S',
        'P4M67D',
        'PT4M89S',
    ];

    protected const WRONG_DURS = [
        '4MT56H0S',
        'P4M56H0S',
        'just wrong'
    ];

    protected const CORRECT_DATES = [
        '2001',
        '2013-07-09',
        '2001-12-01T23:56:01.1234Z',
    ];

    protected const WRONG_DATES = [
        '2001-13897877-01T23:56:01.1234Z',
        '2001-13897877-0123:56:01.1234Z',
        'something else',
    ];

    public function testMatchesDurationPattern(): void
    {
        $helper = new DataHelper();
        foreach (self::CORRECT_DURS as $dur) {
            $this->assertTrue($helper->matchesDurationPattern($dur));
        }
        foreach (self::WRONG_DURS as $dur) {
            $this->assertFalse($helper->matchesDurationPattern($dur));
        }
    }

    public function testMatchesDatetimePattern(): void
    {
        $helper = new DataHelper();
        foreach (self::CORRECT_DATES as $date) {
            $this->assertTrue($helper->matchesDatetimePattern($date));
        }
        foreach (self::WRONG_DATES as $date) {
            $this->assertFalse($helper->matchesDatetimePattern($date));
        }
    }

    public function testDurationToIterator(): void
    {
        $helper = new DataHelper();
        $exp = [
            ['12', '4', '2', '56', '900001', '0'],
            [null, '4', null, '56', null, '0'],
            [null, '4', '67', null, null, null],
            [null, null, null, null, '4', '89']
        ];
        foreach (self::CORRECT_DURS as $index => $dur) {
            $this->assertSame(
                $exp[$index],
                iterator_to_array($helper->durationToIterator($dur))
            );
        }
        $this->assertEmpty(iterator_to_array($helper->durationToIterator(self::WRONG_DURS[0])));
    }

    public function testDurationToSeconds(): void
    {
        $helper = new DataHelper();
        $this->assertSame(
            89 + 4 * 60,
            $helper->durationToSeconds(self::CORRECT_DURS[3])
        );
        $this->assertSame(
            56 * 3600 + 4 * 30 * 24 * 3600,
            $helper->durationToSeconds(self::CORRECT_DURS[1])
        );
    }

    public function testDatetimeToIterator(): void
    {
        $helper = new DataHelper();
        $exp = [
            ['2001', null, null, null, null, null, null, null],
            ['2013', '07', '09', null, null, null, null, null],
            ['2001', '12', '01', '23', '56', '01', '1234', 'Z']
        ];
        foreach (self::CORRECT_DATES as $index => $date) {
            $this->assertSame(
                $exp[$index],
                iterator_to_array($helper->datetimeToIterator($date))
            );
        }
        $this->assertEmpty(iterator_to_array($helper->datetimeToIterator(self::WRONG_DATES[0])));
    }

    public function testDatetimeToObject(): void
    {
        $helper = new DataHelper();
        $exp = [
            '2001-01-01',
            '2013-07-09',
            '2001-12-01',
        ];
        foreach (self::CORRECT_DATES as $index => $date) {
            $this->assertSame(
                $exp[$index],
                $helper->datetimeToObject($date)->format('Y-m-d')
            );
        }
        $this->assertSame(
            '0000-01-01',
            $helper->datetimeToObject(self::WRONG_DATES[0])->format('Y-m-d')
        );
    }

    public function testDurationFromIntegers(): void
    {
        $helper = new DataHelper();
        $int_arrays = [
            [12, 4, 2, 56, 900001, 0],
            [null, 4, null, 56, null, 0],
            [null, 4, 67, null, null, null],
            [null, null, null, null, 4, 89]
        ];
        foreach ($int_arrays as $index => $int_array) {
            $this->assertSame(
                self::CORRECT_DURS[$index],
                $helper->durationFromIntegers(...$int_array)
            );
        }
    }

    public function testDatetimeFromObject(): void
    {
        $helper = new DataHelper();
        $date = new \DateTimeImmutable('2013-07-09');
        $this->assertSame(
            '2013-07-09',
            $helper->datetimeFromObject($date)
        );
    }
}
