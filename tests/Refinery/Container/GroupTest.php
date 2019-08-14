<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\Container;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Container\Group;
use ILIAS\Refinery\Container\AddLabels;
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

    /**
     * @var \ilLanguage
     */
    private $language;

    public function setUp() : void
    {
        $this->dataFactory = new Factory();
        $this->language    = $this->getMockBuilder('\ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new Group($this->dataFactory);
    }

    public function testCustomConstraint()
    {
        $instance = $this->group->addLabels(array('hello', 'world'));
        $this->assertInstanceOf(AddLabels::class, $instance);
    }
}
