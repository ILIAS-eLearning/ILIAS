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

use ILIAS\Data\Range;
use PHPUnit\Framework\TestCase;

/**
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class RangeTest extends TestCase
{
    public function testFactory(): Range
    {
        $f = new ILIAS\Data\Factory();
        $range = $f->range(1, 2);
        $this->assertInstanceOf(Range::class, $range);
        return $range;
    }

    #[\PHPUnit\Framework\Attributes\Depends('testFactory')]
    public function testValues(Range $range): void
    {
        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(2, $range->getLength());
    }

    #[\PHPUnit\Framework\Attributes\Depends('testFactory')]
    public function testEndCalculation(Range $range): void
    {
        $this->assertEquals(3, $range->getEnd());
    }

    #[\PHPUnit\Framework\Attributes\Depends('testFactory')]
    public function testWithLength(Range $range): Range
    {
        $range = $range->withLength(3);
        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(3, $range->getLength());
        $this->assertEquals(4, $range->getEnd());
        return $range;
    }

    #[\PHPUnit\Framework\Attributes\Depends('testWithLength')]
    public function testWithStart(Range $range): Range
    {
        $range = $range->withStart(3);
        $this->assertEquals(3, $range->getStart());
        $this->assertEquals(3, $range->getLength());
        $this->assertEquals(6, $range->getEnd());
        return $range;
    }

    #[\PHPUnit\Framework\Attributes\Depends('testWithStart')]
    public function testUnpack(Range $range): void
    {
        $this->assertEquals(
            [3,3],
            $range->unpack()
        );
    }

    #[\PHPUnit\Framework\Attributes\Depends('testFactory')]
    public function testNegativeStart(Range $range): void
    {
        $this->expectException(InvalidArgumentException::class);
        $range = $range->withStart(-5);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testFactory')]
    public function testNegativeLength(Range $range): void
    {
        $this->expectException(InvalidArgumentException::class);
        $range = $range->withLength(-1);
    }

    public function testConstructionWrongStart(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $f = new ILIAS\Data\Factory();
        $range = $f->range(-1, 2);
    }

    public function testConstructionWrongLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $f = new ILIAS\Data\Factory();
        $range = $f->range(1, -2);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cropCases')]
    public function testCroppedTo($start, $length, $max, $has_changed): void
    {
        $f = new ILIAS\Data\Factory();
        $range = $f->range($start, $length);
        $cropped = $range->croppedTo($max);

        if (!$has_changed) {
            $this->assertEquals($range, $cropped);
        } else {
            $this->assertEquals(min($max, $start), $cropped->getStart());
            $this->assertEquals($max, $cropped->getEnd());
        }
    }

    public static function cropCases(): array
    {
        return [
            [0, 100, 1000, false],
            [0, 1000, 100, true],
            [50, 100, 75, true],
            [50, 100, 200, false],
            [100, 100, 50, true]
        ];
    }
}
