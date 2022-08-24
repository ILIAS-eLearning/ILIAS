<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestGradingMessageBuilderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestGradingMessageBuilderTest extends ilTestBaseTestCase
{
    private ilTestGradingMessageBuilder $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestGradingMessageBuilder(
            $this->createMock(ilLanguage::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestGradingMessageBuilder::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId(2120);
        $this->assertEquals(2120, $this->testObj->getActiveId());
    }
}
