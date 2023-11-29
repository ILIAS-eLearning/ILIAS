<?php

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

declare(strict_types=1);

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
            $this->createMock(\ILIAS\DI\LoggingServices::class),
            0,
            0,
            $this->createMock(\ILIAS\Skill\Service\SkillProfileService::class),
            $this->createMock(\ILIAS\Skill\Service\SkillPersonalService::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillEvaluation::class, $this->testObj);
    }

    public function testUserId(): void
    {
        $userId = 125;
        $this->testObj->setUserId($userId);
        $this->assertEquals($userId, $this->testObj->getUserId());
    }

    public function testActiveId(): void
    {
        $activeId = 125;
        $this->testObj->setActiveId($activeId);
        $this->assertEquals($activeId, $this->testObj->getActiveId());
    }

    public function testPass(): void
    {
        $pass = 125;
        $this->testObj->setPass($pass);
        $this->assertEquals($pass, $this->testObj->getPass());
    }

    public function testNumRequiredBookingsForSkillTriggering(): void
    {
        $numRequiredBookingsForSkillTriggering = 125;
        $this->testObj->setNumRequiredBookingsForSkillTriggering($numRequiredBookingsForSkillTriggering);
        $this->assertEquals($numRequiredBookingsForSkillTriggering, $this->testObj->getNumRequiredBookingsForSkillTriggering());
    }
}
