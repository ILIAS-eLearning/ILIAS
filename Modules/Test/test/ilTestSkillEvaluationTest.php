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
            0,
            0,
            $this->createMock(\ILIAS\Skill\Service\SkillProfileService::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillEvaluation::class, $this->testObj);
    }

    public function testUserId() : void
    {
        $this->testObj->setUserId(125);
        $this->assertEquals(125, $this->testObj->getUserId());
    }

    public function testActiveId() : void
    {
        $this->testObj->setActiveId(125);
        $this->assertEquals(125, $this->testObj->getActiveId());
    }

    public function testPass() : void
    {
        $this->testObj->setPass(125);
        $this->assertEquals(125, $this->testObj->getPass());
    }

    public function testNumRequiredBookingsForSkillTriggering() : void
    {
        $this->testObj->setNumRequiredBookingsForSkillTriggering(125);
        $this->assertEquals(125, $this->testObj->getNumRequiredBookingsForSkillTriggering());
    }
}
