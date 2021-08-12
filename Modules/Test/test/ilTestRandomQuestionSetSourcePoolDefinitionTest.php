<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinition $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinition(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinition::class, $this->testObj);
    }
}