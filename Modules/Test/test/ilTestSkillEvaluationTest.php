<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilTestSkillEvaluationTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationTest extends ilTestBaseTestCase
{
    private ilTestSkillEvaluation $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillEvaluation(
            $this->createMock(ilDBInterface::class),
            0,
            0,
            $this->createMock(\ILIAS\Skill\Service\SkillProfileService::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillEvaluation::class, $this->testObj);
    }

    public function testUserId(): void
    {
        $this->testObj->setUserId(125);
        $this->assertEquals(125, $this->testObj->getUserId());
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId(125);
        $this->assertEquals(125, $this->testObj->getActiveId());
    }

    public function testPass(): void
    {
        $this->testObj->setPass(125);
        $this->assertEquals(125, $this->testObj->getPass());
    }

    public function testNumRequiredBookingsForSkillTriggering(): void
    {
        $this->testObj->setNumRequiredBookingsForSkillTriggering(125);
        $this->assertEquals(125, $this->testObj->getNumRequiredBookingsForSkillTriggering());
    }
}
