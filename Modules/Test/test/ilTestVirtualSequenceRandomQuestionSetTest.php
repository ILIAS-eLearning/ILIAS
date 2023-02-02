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
 * Class ilTestVirtualSequenceRandomQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestVirtualSequenceRandomQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestVirtualSequenceRandomQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestVirtualSequenceRandomQuestionSet(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestSequenceFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestVirtualSequenceRandomQuestionSet::class, $this->testObj);
    }
}
