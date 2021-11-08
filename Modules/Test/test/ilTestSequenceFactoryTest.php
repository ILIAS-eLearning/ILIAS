<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSequenceFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceFactoryTest extends ilTestBaseTestCase
{
    private ilTestSequenceFactory $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSequenceFactory(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSequenceFactory::class, $this->testObj);
    }
}
