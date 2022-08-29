<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestInfoScreenToolbarFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestInfoScreenToolbarFactoryTest extends ilTestBaseTestCase
{
    private ilTestInfoScreenToolbarFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestInfoScreenToolbarFactory();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestInfoScreenToolbarFactory::class, $this->testObj);
    }

    public function testTestRefId(): void
    {
        $this->testObj->setTestRefId(125);
        $this->assertEquals(125, $this->testObj->getTestRefId());
    }

    public function testTestOBJ(): void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestOBJ($objTest_mock);
        $this->assertEquals($objTest_mock, $this->testObj->getTestOBJ());
    }
}
