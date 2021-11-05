<?php

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery\Random\Transformation;

use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Refinery\Random\Effect\ShuffleEffect;
use ILIAS\Data\NotOKException;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use PHPUnit\Framework\TestCase;

class ShuffleTransformationTest extends TestCase
{
    public function testTransformResultSuccess() : void
    {
        $value = ['nrrrrg'];
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::never())->method('seedRandomGenerator');

        $result = (new ShuffleTransformation($seedMock))->transform($value);
        $this->assertInstanceOf(ShuffleEffect::class, $result);
    }

    public function testTransformResultFailure() : void
    {
        $this->expectException(NotOKException::class);
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::never())->method('seedRandomGenerator');

        $result = (new ShuffleTransformation($seedMock))->transform('im no array');
    }
}
