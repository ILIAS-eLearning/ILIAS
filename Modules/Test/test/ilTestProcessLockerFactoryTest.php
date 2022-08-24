<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestProcessLockerFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestProcessLockerFactoryTest extends ilTestBaseTestCase
{
    private ilTestProcessLockerFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestProcessLockerFactory(
            $lng_mock = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilDBInterface::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestProcessLockerFactory::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $lockerFactory = $this->testObj->withContextId(212);
        $this->assertEquals(212, $lockerFactory->getContextId());
    }
}
