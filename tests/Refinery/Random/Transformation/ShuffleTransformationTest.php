<?php

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery;

use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Data\NotOKException;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\ConstraintViolationException;

class ShuffleTransformationTest extends TestCase
{
    public function testTransformResultSuccess() : void
    {
        $seed = 0;
        $value = ['Donec', 'at', 'pede', 'Phasellus', 'purus', 'Nulla', 'facilisis', 'risus', 'a', 'rhoncus', 'fermentum', 'tellus', 'tellus', 'lacinia', 'purus', 'et', 'dictum', 'nunc', 'justo', 'sit', 'amet', 'elit'];
        $expected = $this->shuffleWithSeed($value, $seed);
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::once())->method('seedRandomGenerator')->willReturnCallback(static function () use ($seed) : void {
            \mt_srand($seed);
        });

        $result = (new ShuffleTransformation($seedMock))->transform($value);
        $this->assertEquals($expected, $result);
    }

    public function testTransformResultFailure() : void
    {
        $this->expectException(ConstraintViolationException::class);
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::never())->method('seedRandomGenerator');

        $result = (new ShuffleTransformation($seedMock))->transform('im no array');
    }

    private function shuffleWithSeed(array $array, int $seed) : array
    {
        \mt_srand($seed);
        \shuffle($array);

        return $array;
    }
}
