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

namespace ILIAS\Tests\Refinery\Random;

use ILIAS\Refinery\Random\Group as RandomGroup;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Refinery\Random\Transformation\ShuffleTransformation;
use ILIAS\Refinery\IdentityTransformation;

class GroupTest extends TestCase
{
    private RandomGroup $group;

    protected function setUp(): void
    {
        $this->group = new RandomGroup();
    }

    public function testShuffle(): void
    {
        $mock = $this->getMockBuilder(Seed::class)->getMock();
        $mock->expects(self::never())->method('seedRandomGenerator');
        $instance = $this->group->shuffleArray($mock);
        $this->assertInstanceOf(ShuffleTransformation::class, $instance);
    }

    public function testDontShuffle(): void
    {
        $instance = $this->group->dontShuffle();
        $this->assertInstanceOf(IdentityTransformation::class, $instance);
    }
}
