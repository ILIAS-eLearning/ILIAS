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

/**
 * Class ilTestSequenceTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceTest extends ilTestBaseTestCase
{
    private ilTestSequence $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->testObj = new ilTestSequence($DIC['ilDB'], 0, 0, $this->createMock(\ILIAS\TestQuestionPool\QuestionInfoService::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSequence::class, $this->testObj);
    }
}
