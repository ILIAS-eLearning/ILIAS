<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestEvaluationDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationData $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestEvaluationData();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestEvaluationData::class, $this->testObj);
    }
}