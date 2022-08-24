<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantAccessFilterTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantAccessFilterTest extends ilTestBaseTestCase
{
    private ilTestParticipantAccessFilter $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantAccessFilter();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantAccessFilter::class, $this->testObj);
    }

    public function testRefId(): void
    {
        $this->testObj->setRefId(125);
        $this->assertEquals(125, $this->testObj->getRefId());
    }

    public function testFilter(): void
    {
        $this->testObj->setFilter("testFilter");
        $this->assertEquals("testFilter", $this->testObj->getFilter());
    }
}
