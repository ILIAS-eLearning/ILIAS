<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestObjectiveOrientedContainerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestObjectiveOrientedContainerTest extends ilTestBaseTestCase
{
    private ilTestObjectiveOrientedContainer $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestObjectiveOrientedContainer();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestObjectiveOrientedContainer::class, $this->testObj);
    }

    public function testObjId(): void
    {
        $this->testObj->setObjId(125);
        $this->assertEquals(125, $this->testObj->getObjId());
    }

    public function testRefId(): void
    {
        $this->testObj->setRefId(125);
        $this->assertEquals(125, $this->testObj->getRefId());
    }

    public function testIsObjectiveOrientedPresentationRequired(): void
    {
        $this->assertFalse($this->testObj->isObjectiveOrientedPresentationRequired());

        $this->testObj->setObjId(1254);
        $this->assertTrue($this->testObj->isObjectiveOrientedPresentationRequired());
    }
}
