<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetStagingPoolQuestionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetStagingPoolQuestionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetStagingPoolQuestion $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetStagingPoolQuestion($this->createMock(ilDBInterface::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetStagingPoolQuestion::class, $this->testObj);
    }
}