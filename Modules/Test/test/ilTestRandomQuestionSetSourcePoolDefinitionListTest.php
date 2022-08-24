<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestRandomQuestionSetSourcePoolDefinitionFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionList::class, $this->testObj);
    }

    public function testAddDefinition(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $this->testObj->addDefinition($expected);

        $this->assertEquals($expected, $this->testObj->getDefinition(20));
    }

    public function testSetTrashedPools(): void
    {
        $poolIds = [12, 22, 16];

        $this->testObj->setTrashedPools($poolIds);

        $this->assertEquals($poolIds, $this->testObj->getTrashedPools());
    }

    public function testIsTrashedPool(): void
    {
        $poolIds = [12, 22, 16];

        $this->testObj->setTrashedPools($poolIds);

        $this->assertTrue($this->testObj->isTrashedPool(0));
        $this->assertFalse($this->testObj->isTrashedPool(4));
    }

    public function testHasTrashedPool(): void
    {
        $poolIds = [12, 22, 16];

        $this->testObj->setTrashedPools($poolIds);

        $this->assertTrue($this->testObj->hasTrashedPool());
    }

    public function testHasDefinition(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $this->testObj->addDefinition($expected);

        $this->assertTrue($this->testObj->hasDefinition(20));
    }

    public function testGetDefinition(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $this->testObj->addDefinition($expected);

        $this->assertEquals($expected, $this->testObj->getDefinition(20));
    }

    public function testGetDefinitionBySourcePoolId(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $expected->setPoolId(11);
        $this->testObj->addDefinition($expected);

        $this->assertEquals($expected, $this->testObj->getDefinitionBySourcePoolId(11));
    }

    public function testGetDefinitionIds(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $expected->setPoolId(11);
        $this->testObj->addDefinition($expected);

        $this->assertEquals([20], $this->testObj->getDefinitionIds());
    }

    public function testGetDefinitionCount(): void
    {
        $expected = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
        $expected->setId(20);
        $expected->setPoolId(11);
        $this->testObj->addDefinition($expected);

        $this->assertEquals(1, $this->testObj->getDefinitionCount());
    }
}
