<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestAccessTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestAccessTest extends ilTestBaseTestCase
{
    private ilTestAccess $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilAccess();

        $this->testObj = new ilTestAccess(0, 0);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestAccess::class, $this->testObj);
    }

    public function testAccess(): void
    {
        $accessHandler_mock = $this->createMock(ilAccessHandler::class);
        $this->testObj->setAccess($accessHandler_mock);

        $this->assertEquals($accessHandler_mock, $this->testObj->getAccess());
    }

    public function testRefId(): void
    {
        $this->testObj->setRefId(120);

        $this->assertEquals(120, $this->testObj->getRefId());
    }

    public function testTestId(): void
    {
        $this->testObj->setTestId(120);

        $this->assertEquals(120, $this->testObj->getTestId());
    }
}
