<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\Logical;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Integer\GreaterThan;
use ILIAS\Refinery\Integer\LessThan;
use ILIAS\Refinery\Logical\LogicalOr;
use ILIAS\Refinery\Logical\Not;
use ILIAS\Refinery\Logical\Parallel;
use ILIAS\Refinery\Logical\Sequential;
use ILIAS\Refinery\Logical\Group;
use ILIAS\Tests\Refinery\TestCase;

require_once('./libs/composer/vendor/autoload.php');

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

    /**
     * @var GreaterThan
     */
    private $greaterThanConstraint;

    /**
     * @var LessThan
     */
    private $lessThanConstaint;

    public function setUp() : void
    {
        $this->dataFactory = new Factory();
        $this->language    = $this->getMockBuilder('\ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->group = new Group($this->dataFactory, $this->language);

        $this->greaterThanConstraint = new GreaterThan(2, $this->dataFactory, $this->language);
        $this->lessThanConstaint = new LessThan(5, $this->dataFactory, $this->language);
    }

    public function testLogicalOrGroup()
    {
        $instance = $this->group->logicalOr(array($this->greaterThanConstraint, $this->lessThanConstaint));
        $this->assertInstanceOf(LogicalOr::class, $instance);
    }

    public function testNotGroup()
    {
        $instance = $this->group->not($this->greaterThanConstraint);
        $this->assertInstanceOf(Not::class, $instance);
    }

    public function testParallelGroup()
    {
        $instance = $this->group->parallel(array($this->greaterThanConstraint, $this->lessThanConstaint));
        $this->assertInstanceOf(Parallel::class, $instance);
    }

    public function testSequentialGroup()
    {
        $instance = $this->group->sequential(array($this->greaterThanConstraint, $this->lessThanConstaint));
        $this->assertInstanceOf(Sequential::class, $instance);
    }
}
