<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestEvaluationUserDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationUserDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationUserData $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestEvaluationUserData(0);
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestEvaluationUserData::class, $this->testObj);
    }
}