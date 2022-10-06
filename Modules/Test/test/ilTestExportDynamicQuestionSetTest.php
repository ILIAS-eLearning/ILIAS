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
 * Class ilTestExportDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestExportDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestExportDynamicQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilErr();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilias();
        $this->addGlobal_lng();

        $objTest = $this->createMock(ilObjTest::class);
        $this->testObj = new ilTestExportDynamicQuestionSet(
            $objTest
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestExportDynamicQuestionSet::class, $this->testObj);
    }
}
