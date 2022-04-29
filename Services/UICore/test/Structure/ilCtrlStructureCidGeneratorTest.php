<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilCtrlStructureCidGeneratorTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureCidGeneratorTest extends TestCase
{
    public function testCidGeneratorIncrements() : void
    {
        $generator = new ilCtrlStructureCidGenerator();
        $first_cid = $generator->getCid();
        $next_cid = $generator->getCid();

        $this->assertEquals('0', $first_cid);
        $this->assertNotEquals('0', $next_cid);
        $this->assertEquals('1', $next_cid);
    }

    public function testCidGeneratorPositiveContinuousIncrements() : void
    {
        $generator = new ilCtrlStructureCidGenerator(100);
        $first_cid = $generator->getCid();
        $next_cid = $generator->getCid();

        $this->assertEquals('2s', $first_cid);
        $this->assertNotEquals('2s', $next_cid);
        $this->assertEquals('2t', $next_cid);
    }

    public function testCidGeneratorNegativeContinuousIncrements() : void
    {
        $generator = new ilCtrlStructureCidGenerator(-100);
        $first_cid = $generator->getCid();
        $next_cid = $generator->getCid();

        $this->assertEquals('-2s', $first_cid);
        $this->assertNotEquals('-2s', $next_cid);
        $this->assertEquals('-2r', $next_cid);
    }

    public function testCidGeneratorContinuousIncrementsFromMaxIntegerValue() : void
    {
        $generator = new ilCtrlStructureCidGenerator(9223372036854775807);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Cannot increment property ilCtrlStructureCidGenerator::$index of type int past its maximal value');
        $cid = $generator->getCid();
    }

    public function testCidGeneratorIndexByCidValue() : void
    {
        $generator = new ilCtrlStructureCidGenerator();

        $this->assertEquals(99999, $generator->getIndexByCid('255r'));
        $this->assertEquals(-99999, $generator->getIndexByCid('-255r'));
        $this->assertEquals(0, $generator->getIndexByCid('0'));
        $this->assertEquals(0, $generator->getIndexByCid('-0'));
        $this->assertEquals(1, $generator->getIndexByCid('1'));
        $this->assertEquals(-1, $generator->getIndexByCid('-1'));
    }

    public function testCidGeneratorCidByIndexValue() : void
    {
        $generator = new ilCtrlStructureCidGenerator();

        $this->assertEquals('255r', $generator->getCidByIndex(99999));
        $this->assertEquals('-255r', $generator->getCidByIndex(-99999));
        $this->assertEquals('0', $generator->getCidByIndex(0));
        $this->assertEquals('0', $generator->getCidByIndex(-0));
        $this->assertEquals('1', $generator->getCidByIndex(1));
        $this->assertEquals('-1', $generator->getCidByIndex(-1));
    }
}
