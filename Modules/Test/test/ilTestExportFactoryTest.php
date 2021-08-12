<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestExportFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExportFactoryTest extends ilTestBaseTestCase
{
    private ilTestExportFactory $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestExportFactory($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestExportFactory::class, $this->testObj);
    }
}