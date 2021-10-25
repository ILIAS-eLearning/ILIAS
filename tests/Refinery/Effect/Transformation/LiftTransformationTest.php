<?php

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery\Effect\Transformation;

use ILIAS\Data\NotOKException;
use ILIAS\Data\Result\OK;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\Effect\Transformation\LiftTransformation;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Effect\Effect;

class LiftTransformationTest extends TestCase
{
    public function testTransform() : void
    {
        $value = 'hejaaa';

        $actual = (new LiftTransformation())->transform($value);

        $this->assertInstanceOf(Effect::class, $actual);
        $this->assertEquals($value, $actual->value());
    }

    public function testInvoke() : void
    {
        $value = ['bababa', 'uauauau'];

        $actual = (new LiftTransformation())($value);

        $this->assertInstanceOf(Effect::class, $actual);
        $this->assertEquals($value, $actual->value());
    }

    public function testTransformResult() : void
    {
        $value = 678;

        $actual = (new LiftTransformation())->transformResult($value);

        $this->assertInstanceOf(OK::class, $actual);
        $this->assertInstanceOf(Effect::class, $actual->value());
        $this->assertEquals($value, $actual->value()->value());
    }

    public function testApplyToOk() : void
    {
        $value = ['im in an array'];
        $result = (new LiftTransformation())->applyTo(new OK($value));
        $this->assertInstanceOf(OK::class, $result);
        $this->assertInstanceOf(Effect::class, $result->value());
        $this->assertEquals($value, $result->value()->value());
    }

    public function testApplyToError() : void
    {
        $error = new Error('some error');
        $result = (new LiftTransformation())->applyTo($error);
        $this->assertEquals($error, $result);
    }
}
