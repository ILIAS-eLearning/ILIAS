<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestTabsManagerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestTabsManagerTest extends ilTestBaseTestCase
{
    private ilTestTabsManager $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_ilTabs();
        $this->addGlobal_ilAccess();
        $this->addGlobal_lng();

        $this->testObj = new ilTestTabsManager(
            $this->createMock(ilTestAccess::class),
            $this->createMock(ilTestObjectiveOrientedContainer::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestTabsManager::class, $this->testObj);
    }
}