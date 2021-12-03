<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\Integer;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Integer\Group;
use ILIAS\Refinery\Integer\GreaterThan;
use ILIAS\Refinery\Integer\LessThan;
use ILIAS\Tests\Refinery\TestCase;
use ILIAS\Refinery\Integer\GreaterThanOrEqual;

class GroupTest extends TestCase
{
    private Group $group;

    public function setUp() : void
    {
        $dataFactory = new Factory();
        $language = $this->getMockBuilder('\ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new Group($dataFactory, $language);
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
}
