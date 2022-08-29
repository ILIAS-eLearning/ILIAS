<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionFactoryTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionFactory::class, $this->testObj);
    }

    public function testGetSourcePoolDefinitionByOriginalPoolData(): void
    {
        $originalPoolData = [
            "qpl_id" => 2,
            'qpl_ref_id' => 4711,
            "qpl_title" => "testTitle",
            "qpl_path" => "test/path",
            "count" => 5
        ];

        $result = $this->testObj->getSourcePoolDefinitionByOriginalPoolData($originalPoolData);
        $this->assertEquals($originalPoolData["qpl_id"], $result->getPoolId());
        $this->assertEquals($originalPoolData["qpl_ref_id"], $result->getPoolRefId());
        $this->assertEquals($originalPoolData["qpl_title"], $result->getPoolTitle());
        $this->assertEquals($originalPoolData["qpl_path"], $result->getPoolPath());
        $this->assertEquals($originalPoolData["count"], $result->getPoolQuestionCount());
    }

    public function testGetEmptySourcePoolDefinition(): void
    {
        $this->assertInstanceOf(
            ilTestRandomQuestionSetSourcePoolDefinition::class,
            $this->testObj->getEmptySourcePoolDefinition()
        );
    }
}
