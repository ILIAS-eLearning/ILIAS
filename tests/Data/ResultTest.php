<?php

declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

/**
 * Tests working with result object
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ResultTest extends TestCase
{
    private ?Data\Factory $f;

    protected function setUp(): void
    {
        $this->f = new Data\Factory();
    }

    protected function tearDown(): void
    {
        $this->f = null;
    }

    public function testValue(): void
    {
        $result = $this->f->ok(3.154);
        $this->assertEquals(3.154, $result->value());
    }

    public function testNoValue(): void
    {
        $result = $this->f->error("Something went wrong");

        try {
            $result->value();
            $raised = false;
        } catch (Exception $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }

    public function testIsOk(): void
    {
        $result = $this->f->ok(3.154);
        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isError());
    }

    public function testError(): void
    {
        $result = $this->f->error("Something went wrong");
        $this->assertEquals("Something went wrong", $result->error());
    }

    public function testNoError(): void
    {
        $result = $this->f->ok(3.154);

        try {
            $result->error();
            $raised = false;
        } catch (LogicException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }

    public function testIsError(): void
    {
        $result = $this->f->error("Something went wrong");
        $this->assertTrue($result->isError());
        $this->assertFalse($result->isOk());
    }

    public function testValueOr(): void
    {
        $result = $this->f->ok(3.154);
        $this->assertEquals(3.154, $result->valueOr(5));
    }

    public function testValueOrDefault(): void
    {
        $result = $this->f->error("Something went wrong");
        $this->assertEquals(5, $result->valueOr(5));
    }

    public function testMapOk(): void
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

    public function testMapError(): void
    {
        $result = $this->f->error("Something went wrong");
        $multiplicator = 3;
        $new_result = $result->map(function ($v) use ($multiplicator) {
            return $v * $multiplicator;
        });

        $this->assertEquals($result, $new_result);
    }

    public function testThenOk(): void
    {
        $result = $this->f->ok(3);
        $multiplicator = 3;
        $new_result = $result->then(function ($v) use ($multiplicator) {
            return $this->f->ok(($v * $multiplicator));
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertNotEquals($result, $new_result);
        $this->assertEquals(9, $new_result->value());
    }

    public function testThenCallableNull(): void
    {
        $result = $this->f->ok(3);
        $new_result = $result->then(function ($v) {
            return null;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testThenError(): void
    {
        $result = $this->f->error("Something went wrong");
        $multiplicator = 3;
        $new_result = $result->then(function ($v) use ($multiplicator) {
            return $this->f->ok(($v * $multiplicator));
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testThenNoResult(): void
    {
        $result = $this->f->ok(3);

        try {
            $new_result = $result->then(function ($v) {
                return 4;
            });

            $raised = false;
        } catch (UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }

    public function testExceptError(): void
    {
        $result = $this->f->error("Something went wrong");
        $exception = "Something else went wrong";

        $new_result = $result->except(function ($v) use ($exception) {
            return $this->f->error($exception);
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertNotEquals($result, $new_result);
        $this->assertEquals("Something else went wrong", $new_result->error());
    }

    public function testExceptCallableNull(): void
    {
        $result = $this->f->error("Something went wrong");
        $exception = "Something else went wrong";

        $new_result = $result->except(function ($v) {
            return null;
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testExceptOk(): void
    {
        $result = $this->f->ok(3);
        $exception = "Something else went wrong";

        $new_result = $result->except(function ($v) use ($exception) {
            return $this->f->error($exception);
        });

        $this->assertInstanceOf(Data\Result::class, $new_result);
        $this->assertEquals($result, $new_result);
    }

    public function testExceptNoResult(): void
    {
        $result = $this->f->error("Something went wrong");

        try {
            $new_result = $result->except(function ($v) {
                return "New error text";
            });

            $raised = false;
        } catch (UnexpectedValueException $e) {
            $raised = true;
        }

        $this->assertTrue($raised);
    }
}
