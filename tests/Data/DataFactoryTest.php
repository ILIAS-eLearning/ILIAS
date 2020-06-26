<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

/**
 * Testing the faytory of result objects
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class DataFactoryTest extends TestCase
{

    /**
     * @var Data\Factory
     */
    private $f;

    protected function setUp() : void
    {
        $this->f = new Data\Factory();
    }

    protected function tearDown() : void
    {
        $this->f = null;
    }

    public function testOk()
    {
        $result = $this->f->ok(3.154);
        $this->assertInstanceOf(Data\Result::class, $result);
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isError());
    }

    public function testError()
    {
        $result = $this->f->error("Something went wrong");
        $this->assertInstanceOf(Data\Result::class, $result);
        $this->assertTrue($result->isError());
        $this->assertFalse($result->isOk());
    }

    public function testPassword()
    {
        $pwd = $this->f->password("secret");
        $this->assertInstanceOf(Data\Password::class, $pwd);
    }

    public function testAlphanumeric()
    {
        $dataType = $this->f->alphanumeric('someValue');
        $this->assertInstanceOf(Data\Alphanumeric::class, $dataType);
    }

    public function testPositiveInteger()
    {
        $dataType = $this->f->positiveInteger(100);
        $this->assertInstanceOf(Data\PositiveInteger::class, $dataType);
    }

    public function testIntegerRange()
    {
        $dataType = $this->f->openedIntegerInterval(1, 100);
        $this->assertInstanceOf(Data\Interval\OpenedIntegerInterval::class, $dataType);
    }

    public function testStrictIntegerRange()
    {
        $dataType = $this->f->closedIntegerInterval(1, 100);
        $this->assertInstanceOf(Data\Interval\ClosedIntegerInterval::class, $dataType);
    }

    public function testFloatRange()
    {
        $dataType = $this->f->openedFloatInterval(1.4, 100.2);
        $this->assertInstanceOf(Data\Interval\OpenedFloatInterval::class, $dataType);
    }

    public function testStrictFloatRange()
    {
        $dataType = $this->f->closedFloatInterval(1.4, 100.2);
        $this->assertInstanceOf(Data\Interval\ClosedFloatInterval::class, $dataType);
    }

    public function testDataSize1()
    {
        $dataType = $this->f->dataSize(10, "MB");
        $this->assertInstanceOf(Data\DataSize::class, $dataType);
    }

    public function testDataSize2()
    {
        $dataType = $this->f->dataSize("10G");
        $this->assertEquals(10, $dataType->getSize());
        $this->assertEquals(Data\DataSize::GiB, $dataType->getUnit());
        $this->assertEquals(10 * Data\DataSize::GiB, $dataType->inBytes());
        $this->assertInstanceOf(Data\DataSize::class, $dataType);
    }
}
