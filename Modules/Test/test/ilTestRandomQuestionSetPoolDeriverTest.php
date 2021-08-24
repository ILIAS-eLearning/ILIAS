<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetPoolDeriverTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetPoolDeriverTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetPoolDeriver $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetPoolDeriver(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetPoolDeriver::class, $this->testObj);
    }

    public function testTargetContainerRef() : void
    {
        $this->testObj->setTargetContainerRef(125);
        $this->assertEquals(125, $this->testObj->getTargetContainerRef());
    }

    public function testOwnerId() : void
    {
        $this->testObj->setOwnerId(125);
        $this->assertEquals(125, $this->testObj->getOwnerId());
    }

    public function testSourcePoolDefinitionList() : void
    {
        $mock = $this->createMock(ilTestRandomQuestionSetSourcePoolDefinitionList::class);
        $this->testObj->setSourcePoolDefinitionList($mock);
        $this->assertEquals($mock, $this->testObj->getSourcePoolDefinitionList());
    }
}