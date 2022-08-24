<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestEvaluationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationTest extends ilTestBaseTestCase
{
    private ilTestEvaluation $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestEvaluation(
            $this->createMock(ilDBInterface::class),
            0
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestEvaluation::class, $this->testObj);
    }
}
