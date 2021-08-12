<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantDataTest extends ilTestBaseTestCase
{
    private ilTestParticipantData $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantData(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipantData::class, $this->testObj);
    }
}