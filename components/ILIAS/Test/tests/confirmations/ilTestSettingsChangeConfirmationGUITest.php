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

use ILIAS\Test\RequestDataCollector;

/**
 * Class ilTestSettingsChangeConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSettingsChangeConfirmationGUITest extends ilTestBaseTestCase
{
    private ilTestSettingsChangeConfirmationGUI $testSettingsChangeConfirmationGUI;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSettingsChangeConfirmationGUI = new ilTestSettingsChangeConfirmationGUI(
            $this->createMock(RequestDataCollector::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function testSetAndGetOldQuestionSetType(): void
    {
        $expect = 'testType';

        $this->testSettingsChangeConfirmationGUI->setOldQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getOldQuestionSetType());
    }

    public function testSetAndGetNewQuestionSetType(): void
    {
        $expect = 'testType';

        $this->testSettingsChangeConfirmationGUI->setNewQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getNewQuestionSetType());
    }

    public function testSetAndIsQuestionLossInfoEnabled(): void
    {
        $expect = true;

        $this->testSettingsChangeConfirmationGUI->setQuestionLossInfoEnabled($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->isQuestionLossInfoEnabled());
    }
}
