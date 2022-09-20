<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Tests\Refinery\Random\Transformation;

use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Data\NotOKException;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\ConstraintViolationException;

class ShuffleTransformationTest extends TestCase
{
    public function testTransformResultSuccess(): void
    {
        $seed = 0;
        $value = ['Donec', 'at', 'pede', 'Phasellus', 'purus', 'Nulla', 'facilisis', 'risus', 'a', 'rhoncus', 'fermentum', 'tellus', 'tellus', 'lacinia', 'purus', 'et', 'dictum', 'nunc', 'justo', 'sit', 'amet', 'elit'];
        $expected = $this->shuffleWithSeed($value, $seed);
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::once())->method('seedRandomGenerator')->willReturnCallback(static function () use ($seed): void {
            mt_srand($seed);
        });

        $result = (new ShuffleTransformation($seedMock))->transform($value);
        $this->assertEquals($expected, $result);
    }

    public function testTransformResultFailure(): void
    {
        $this->expectException(ConstraintViolationException::class);
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::never())->method('seedRandomGenerator');

        $result = (new ShuffleTransformation($seedMock))->transform('im no array');
    }

    private function shuffleWithSeed(array $array, int $seed): array
    {
        mt_srand($seed);
        shuffle($array);

        return $array;
    }
}
