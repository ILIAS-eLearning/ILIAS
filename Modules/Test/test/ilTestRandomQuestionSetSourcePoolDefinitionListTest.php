<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionList $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestRandomQuestionSetSourcePoolDefinitionFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionList::class, $this->testObj);
    }
}