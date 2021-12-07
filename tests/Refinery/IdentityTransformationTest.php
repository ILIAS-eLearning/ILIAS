<?php

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery\Random\Transformation;

use ILIAS\Data\NotOKException;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\IdentityTransformation;
use PHPUnit\Framework\TestCase;

class IdentityTransformationTest extends TestCase
{
    public function testTransform() : void
    {
        $value = 'hejaaa';

        $actual = (new IdentityTransformation())->transform($value);

        $this->assertEquals($value, $actual);
    }

    public function testApplyToOk() : void
    {
        $value = ['im in an array'];
        $result = (new IdentityTransformation())->applyTo(new Ok($value));
        $this->assertInstanceOf(Ok::class, $result);
        $this->assertEquals($value, $result->value());
    }

    public function testApplyToError() : void
    {
        $error = new Error('some error');
        $result = (new IdentityTransformation())->applyTo($error);
        $this->assertEquals($error, $result);
    }
}
