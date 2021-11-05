<?php

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery\Effect\Transformation;

use ILIAS\Data\NotOKException;
use ILIAS\Data\Result\Ok;
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

    public function testApplyToOk() : void
    {
        $value = ['im in an array'];
        $result = (new LiftTransformation())->applyTo(new Ok($value));
        $this->assertInstanceOf(Ok::class, $result);
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
