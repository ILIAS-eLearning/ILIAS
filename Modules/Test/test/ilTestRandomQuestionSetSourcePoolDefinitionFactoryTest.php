<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionFactoryTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionFactory $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionFactory::class, $this->testObj);
    }
}