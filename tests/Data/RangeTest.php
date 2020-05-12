<?php declare(strict_types=1);

use ILIAS\Data\Range;
use PHPUnit\Framework\TestCase;

/**
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class RangeTest extends TestCase
{
    public function testFactory()
    {
        $f = new ILIAS\Data\Factory();
        $range = $f->range(1, 2);
        $this->assertInstanceOf(Range::class, $range);
        return $range;
    }

    /**
     * @depends testFactory
     */
    public function testValues(Range $range)
    {
        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(2, $range->getLength());
    }

    /**
     * @depends testFactory
     */
    public function testEndCalculation(Range $range)
    {
        $this->assertEquals(3, $range->getEnd());
    }

    /**
     * @depends testFactory
     */
    public function testWithLength(Range $range)
    {
        $range = $range->withLength(3);
        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(3, $range->getLength());
        $this->assertEquals(4, $range->getEnd());
        return $range;
    }

    /**
     * @depends testWithLength
     */
    public function testWithStart(Range $range)
    {
        $range = $range->withStart(3);
        $this->assertEquals(3, $range->getStart());
        $this->assertEquals(3, $range->getLength());
        $this->assertEquals(6, $range->getEnd());
        return $range;
    }

    /**
     * @depends testWithStart
     */
    public function testUnpack(Range $range)
    {
        $this->assertEquals(
            [3,3],
            $range->unpack()
        );
    }

    /**
     * @depends testFactory
     */
    public function testNegativeStart(Range $range)
    {
        $this->expectException(InvalidArgumentException::class);
        $range = $range->withStart(-5);
    }

    /**
     * @depends testFactory
     */
    public function testNegativeLength(Range $range)
    {
        $this->expectException(InvalidArgumentException::class);
        $range = $range->withLength(-1);
    }

    /**
     * @depends testFactory
     */
    public function testNullLength(Range $range)
    {
        $this->expectException(InvalidArgumentException::class);
        $range = $range->withLength(0);
    }

    public function testConstructionWrongStart()
    {
        $this->expectException(InvalidArgumentException::class);
        $f = new ILIAS\Data\Factory();
        $range = $f->range(-1, 2);
    }

    public function testConstructionWrongLength()
    {
        $this->expectException(InvalidArgumentException::class);
        $f = new ILIAS\Data\Factory();
        $range = $f->range(1, -2);
    }

    public function testConstructionNullLength()
    {
        $this->expectException(InvalidArgumentException::class);
        $f = new ILIAS\Data\Factory();
        $range = $f->range(1, 0);
    }
}
