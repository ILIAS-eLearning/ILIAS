<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestObjectiveOrientedContainerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestObjectiveOrientedContainerTest extends ilTestBaseTestCase
{
    private ilTestObjectiveOrientedContainer $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestObjectiveOrientedContainer();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestObjectiveOrientedContainer::class, $this->testObj);
    }
}