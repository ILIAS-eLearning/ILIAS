<?php

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery\Random\Transformation;

use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Refinery\Random\Effect\ShuffleEffect;
use ILIAS\Data\NotOKException;
use ILIAS\Data\Result\OK;
use ILIAS\Data\Result\Error;
use PHPUnit\Framework\TestCase;

class ShuffleTransformationTest extends TestCase
{
    public function testTransformResultSuccess() : void
    {
        $value = ['nrrrrg'];
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::never())->method('seedRandomGenerator');

        $result = (new ShuffleTransformation($seedMock))->transformResult($value);
        $this->assertInstanceOf(OK::class, $result);
        $this->assertInstanceOf(ShuffleEffect::class, $result->value());
    }

    public function testTransformResultFailure() : void
    {
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::never())->method('seedRandomGenerator');

        $result = (new ShuffleTransformation($seedMock))->transformResult('im no array');
        $this->assertInstanceOf(Error::class, $result);
    }
}
