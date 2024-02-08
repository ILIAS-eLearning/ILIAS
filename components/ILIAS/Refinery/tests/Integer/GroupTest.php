<?php

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

declare(strict_types=1);

namespace ILIAS\Tests\Refinery\Integer;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Integer\Group as IntegerGroup;
use ILIAS\Refinery\Integer\GreaterThan;
use ILIAS\Refinery\Integer\LessThan;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Integer\GreaterThanOrEqual;
use ILIAS\Refinery\Integer\LessThanOrEqual;
use ILIAS\Refinery\In\Group as InGroup;
use ILIAS\Refinery\Constraint;
use ILIAS\Language\Language;

class GroupTest extends TestCase
{
    private IntegerGroup $group;

    protected function setUp(): void
    {
        $dataFactory = new Factory();
        $language = $this->getMockBuilder(Language::class)
            ->disableOriginalConstructor()
            ->getMock();
        $in = $this->getMockBuilder(InGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new IntegerGroup($dataFactory, $language, $in);
    }

    public function testGreaterThanInstance(): void
    {
        $instance = $this->group->isGreaterThan(42);
        $this->assertInstanceOf(GreaterThan::class, $instance);
    }
    public function testLowerThanInstance(): void
    {
        $instance = $this->group->isLessThan(42);
        $this->assertInstanceOf(LessThan::class, $instance);
    }

    public function testGreaterThanOrEqualInstance(): void
    {
        $instance = $this->group->isGreaterThanOrEqual(42);
        $this->assertInstanceOf(GreaterThanOrEqual::class, $instance);
    }

    public function testLessThanOrEqualInstance(): void
    {
        $instance = $this->group->isLessThanOrEqual(42);
        $this->assertInstanceOf(LessThanOrEqual::class, $instance);
    }

    public function testIsBetween(): void
    {
        $dataFactory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $language = $this->getMockBuilder(Language::class)->disableOriginalConstructor()->getMock();
        $in = $this->getMockBuilder(InGroup::class)->disableOriginalConstructor()->getMock();
        $series = $this->getMockBuilder(Constraint::class)->getMock();

        $in->expects(self::once())->method('series')->willReturnCallback(function (array $array) use ($series) {
            $this->assertSame(2, count($array));
            $this->assertInstanceOf(GreaterThanOrEqual::class, $array[0]);
            $this->assertInstanceOf(LessThanOrEqual::class, $array[1]);

            return $series;
        });

        $group = new IntegerGroup($dataFactory, $language, $in);
        $this->assertSame($series, $group->isBetween(4, 8));
    }
}
