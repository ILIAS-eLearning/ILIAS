<?php

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Tests\Refinery\Random;

use ILIAS\Refinery\Random\Group;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\Refinery\IdentityTransformation;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    private $group;

    public function setUp() : void
    {
        $this->group = new Group();
    }

    public function testShuffle() : void
    {
        $mock = $this->getMockBuilder(Seed::class)->getMock();
        $mock->expects(self::never())->method('seedRandomGenerator');
        $instance = $this->group->shuffleArray($mock);
        $this->assertInstanceOf(ShuffleTransformation::class, $instance);
    }

    public function testDontShuffle() : void
    {
        $instance = $this->group->dontShuffle();
        $this->assertInstanceOf(IdentityTransformation::class, $instance);
    }
}
