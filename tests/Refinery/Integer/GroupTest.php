<?php declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\Integer;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Integer\Group as IntegerGroup;
use ILIAS\Refinery\Integer\GreaterThan;
use ILIAS\Refinery\Integer\LessThan;
use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\Integer\GreaterThanOrEqual;
use ILIAS\Refinery\Integer\LessThanOrEqual;
use ilLanguage;

class GroupTest extends TestCase
{
    private IntegerGroup $group;

    protected function setUp() : void
    {
        $dataFactory = new Factory();
        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new IntegerGroup($dataFactory, $language);
    }

    public function testGreaterThanInstance() : void
    {
        $instance = $this->group->isGreaterThan(42);
        $this->assertInstanceOf(GreaterThan::class, $instance);
    }
    public function testLowerThanInstance() : void
    {
        $instance = $this->group->isLessThan(42);
        $this->assertInstanceOf(LessThan::class, $instance);
    }

    public function testGreaterThanOrEqualInstance() : void
    {
        $instance = $this->group->isGreaterThanOrEqual(42);
        $this->assertInstanceOf(GreaterThanOrEqual::class, $instance);
    }

    public function testLessThanOrEqualInstance() : void
    {
        $instance = $this->group->isLessThanOrEqual(42);
        $this->assertInstanceOf(LessThanOrEqual::class, $instance);
    }
}
