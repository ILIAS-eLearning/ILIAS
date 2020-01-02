<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;

/**
 * Tests working with result object
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ResultTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->f = new Data\Factory();
    }

    protected function tearDown()
    {
        $this->f = null;
    }

    public function testValue()
    {
        $result = $this->f->ok(3.154);
        $this->assertEquals(3.154, $result->value());
    }

    public function testNoValue()
    {
        $result = $this->f->error("Something went wrong");

        try {
            $result->value();
            $raised = false;
        } catch (\Exception $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }

    public function testIsOk()
    {
        $result = $this->f->ok(3.154);
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isError());
    }

    public function testError()
    {
        $result = $this->f->error("Something went wrong");
        $this->assertEquals("Something went wrong", $result->error());
    }

    public function testNoError()
    {
        $result = $this->f->ok(3.154);

        try {
            $result->error();
            $raised = false;
        } catch (\LogicException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }

    public function testIsError()
    {
        $result = $this->f->error("Something went wrong");
        $this->assertTrue($result->isError());
        $this->assertFalse($result->isOk());
    }

    public function testValueOr()
    {
        $result = $this->f->ok(3.154);
        $this->assertEquals(3.154, $result->valueOr(5));
    }

    public function testValueOrDefault()
    {
        $result = $this->f->error("Something went wrong");
        $this->assertEquals(5, $result->valueOr(5));
    }

    public function testMapOk()
    {
        $result = $this->f->ok(3);
        $multiplicator = 3;
        $new_result = $result->map(function ($v) use ($multiplicator) {
            return $v * $multiplicator;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertNotEquals($result, $new_result);
        $this->assertEquals(9, $new_result->value());
    }

    public function testMapError()
    {
        $result = $this->f->error("Something went wrong");
        $multiplicator = 3;
        $new_result = $result->map(function ($v) use ($multiplicator) {
            return $v * $multiplicator;
        });

        $this->assertEquals($result, $new_result);
    }

    public function testThenOk()
    {
        $result = $this->f->ok(3);
        $multiplicator = 3;
        $new_result = $result->then(function ($v) use ($multiplicator) {
            $ret = $this->f->ok(($v * $multiplicator));
            return $ret;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertNotEquals($result, $new_result);
        $this->assertEquals(9, $new_result->value());
    }

    public function testThenCallableNull()
    {
        $result = $this->f->ok(3);
        $new_result = $result->then(function ($v) {
            return null;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testThenError()
    {
        $result = $this->f->error("Something went wrong");
        $multiplicator = 3;
        $new_result = $result->then(function ($v) use ($multiplicator) {
            $ret = $this->f->ok(($v * $multiplicator));
            return $ret;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testThenNoResult()
    {
        $result = $this->f->ok(3);

        try {
            $new_result = $result->then(function ($v) {
                return 4;
            });

            $raised = false;
        } catch (\UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }

    public function testExceptError()
    {
        $result = $this->f->error("Something went wrong");
        $exception = "Something else went wrong";

        $new_result = $result->except(function ($v) use ($exception) {
            $ret = $this->f->error($exception);
            return $ret;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertNotEquals($result, $new_result);
        $this->assertEquals("Something else went wrong", $new_result->error());
    }

    public function testExceptCallableNull()
    {
        $result = $this->f->error("Something went wrong");
        $exception = "Something else went wrong";

        $new_result = $result->except(function ($v) {
            return null;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testExceptOk()
    {
        $result = $this->f->ok(3);
        $exception = "Something else went wrong";

        $new_result = $result->except(function ($v) use ($exception) {
            $ret = $this->f->error($exception);
            return $ret;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testExceptNoResult()
    {
        $result = $this->f->error("Something went wrong");

        try {
            $new_result = $result->except(function ($v) {
                return "New error text";
            });

            $raised = false;
        } catch (\UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }
}
