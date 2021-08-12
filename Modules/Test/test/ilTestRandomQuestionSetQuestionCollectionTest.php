<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetQuestionCollectionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetQuestionCollectionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetQuestionCollection $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetQuestionCollection();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetQuestionCollection::class, $this->testObj);
    }
}