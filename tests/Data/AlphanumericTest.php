<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Refinery\ConstraintViolationException;
use PHPUnit\Framework\TestCase;

require_once('./libs/composer/vendor/autoload.php');

class AlphanumericTest extends TestCase
{
    public function testSimpleStringIsCorrectAlphanumericValueAndCanBeConvertedToString() : void
    {
        $value = new Alphanumeric('hello');

        $this->assertSame('hello', $value->asString());
    }

    public function testIntegerIsAlphanumericValueAndCanBeConvertedToString() : void
    {
        $value = new Alphanumeric(6);

        $this->assertSame('6', $value->asString());
    }

    public function testIntegerIsAlphanumericValue() : void
    {
        $value = new Alphanumeric(6);

        $this->assertSame(6, $value->getValue());
    }

    public function testFloatIsAlphanumericValueAndCanBeConvertedToString() : void
    {
        $value = new Alphanumeric(6.0);

        $this->assertSame('6', $value->asString());
    }

    public function testFloatIsAlphanumericValue() : void
    {
        $value = new Alphanumeric(6.0);

        $this->assertSame(6.0, $value->getValue());
    }

    public function testTextIsNotAlphanumericAndWillThrowException() : void
    {
        $this->expectNotToPerformAssertions();

        try {
            $value = new Alphanumeric('hello world');
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }
}
