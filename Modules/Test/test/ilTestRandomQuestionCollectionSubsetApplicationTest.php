<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionCollectionSubsetApplicationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionCollectionSubsetApplicationTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionCollectionSubsetApplication $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionCollectionSubsetApplication();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionCollectionSubsetApplication::class, $this->testObj);
    }
}