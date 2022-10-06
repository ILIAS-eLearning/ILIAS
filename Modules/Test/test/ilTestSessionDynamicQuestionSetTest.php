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
 * Class ilTestSessionDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSessionDynamicQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSessionDynamicQuestionSet();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSessionDynamicQuestionSet::class, $this->testObj);
    }

    public function testGetQuestionSetFilterSelection(): void
    {
        $this->assertInstanceOf(
            ilTestDynamicQuestionSetFilterSelection::class,
            $this->testObj->getQuestionSetFilterSelection()
        );
    }

    public function testCurrentQuestionId(): void
    {
        $this->testObj->setCurrentQuestionId(20);
        $this->assertEquals(20, $this->testObj->getCurrentQuestionId());
    }
}
