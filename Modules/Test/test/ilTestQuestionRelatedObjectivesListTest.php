<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestQuestionRelatedObjectivesListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionRelatedObjectivesListTest extends ilTestBaseTestCase
{
    private ilTestQuestionRelatedObjectivesList $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestQuestionRelatedObjectivesList();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestQuestionRelatedObjectivesList::class, $this->testObj);
    }
}