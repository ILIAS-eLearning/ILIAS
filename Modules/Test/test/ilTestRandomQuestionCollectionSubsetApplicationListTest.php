<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionCollectionSubsetApplicationListTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionCollectionSubsetApplicationListTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionCollectionSubsetApplicationList $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionCollectionSubsetApplicationList();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionCollectionSubsetApplicationList::class, $this->testObj);
    }
}
