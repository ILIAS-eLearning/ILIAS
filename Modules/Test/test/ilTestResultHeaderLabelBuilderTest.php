<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultHeaderLabelBuilderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultHeaderLabelBuilderTest extends ilTestBaseTestCase
{
    private ilTestResultHeaderLabelBuilder $testObj;

    private $lng_mock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lng_mock = $this->createMock(ilLanguage::class);

        $this->testObj = new ilTestResultHeaderLabelBuilder(
            $this->lng_mock,
            $this->createMock(ilObjectDataCache::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultHeaderLabelBuilder::class, $this->testObj);
    }

    public function testObjectiveOrientedContainerId(): void
    {
        $this->testObj->setObjectiveOrientedContainerId(5);
        $this->assertEquals(5, $this->testObj->getObjectiveOrientedContainerId());
    }

    public function testTestObjId(): void
    {
        $this->testObj->setTestObjId(5);
        $this->assertEquals(5, $this->testObj->getTestObjId());
    }

    public function testTestRefId(): void
    {
        $this->testObj->setTestRefId(5);
        $this->assertEquals(5, $this->testObj->getTestRefId());
    }

    public function testUserId(): void
    {
        $this->testObj->setUserId(5);
        $this->assertEquals(5, $this->testObj->getUserId());
    }
}
