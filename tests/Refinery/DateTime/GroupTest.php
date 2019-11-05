<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\DateTime;

use ILIAS\Data\Factory;
use ILIAS\Refinery\DateTime\Group;
use ILIAS\Refinery\DateTime\ChangeTimezone;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');
require_once('./tests/Refinery/TestCase.php');

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    private $group;

    /**
     * @var Factory
     */
    private $dataFactory;

    public function setUp() : void
    {
        $this->dataFactory = new Factory();
        $this->group = new Group($this->dataFactory);
    }

    public function testChangeTimezone()
    {
        $instance = $this->group->changeTimezone('Europe/Berlin');
        $this->assertInstanceOf(ChangeTimezone::class, $instance);
    }

    public function testChangeTimezoneWrongConstruction()
    {
        $this->expectException(\InvalidArgumentException::class);
        $instance = $this->group->changeTimezone('MiddleEarth/Minas_Morgul');
    }
}
