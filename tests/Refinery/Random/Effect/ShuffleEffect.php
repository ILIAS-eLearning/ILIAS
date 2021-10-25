<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery\Random\Effect;

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Random\Seed\Seed;

class ShuffleEffect extends TestCase
{
    public function testValue() : void
    {
        $value = ['arrr']
        $seedMock = $this->getMockBuilder(Seed::class)->getMock();
        $seedMock->expects(self::once())->method('seedRandomGenerator');
        $result = (new ShuffleEffect($value, $seedMock))->value();
        $this->assertEquals($value, $result);
    }
}
