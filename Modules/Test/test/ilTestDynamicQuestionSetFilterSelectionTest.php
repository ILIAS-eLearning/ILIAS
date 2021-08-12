<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestDynamicQuestionSetFilterSelectionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestDynamicQuestionSetFilterSelectionTest extends ilTestBaseTestCase
{
    private ilTestDynamicQuestionSetFilterSelection $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestDynamicQuestionSetFilterSelection();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestDynamicQuestionSetFilterSelection::class, $this->testObj);
    }
}