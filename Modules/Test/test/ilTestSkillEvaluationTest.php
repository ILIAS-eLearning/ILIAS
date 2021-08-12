<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillEvaluationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationTest extends ilTestBaseTestCase
{
    private ilTestSkillEvaluation $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillEvaluation(
            $this->createMock(ilDBInterface::class),
            0, 0
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillEvaluation::class, $this->testObj);
    }
}