<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPlayerFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerFactoryTest extends ilTestBaseTestCase
{
    private ilTestPlayerFactory $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestPlayerFactory($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPlayerFactory::class, $this->testObj);
    }
}