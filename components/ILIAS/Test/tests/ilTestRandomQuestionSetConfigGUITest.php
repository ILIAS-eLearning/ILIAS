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
 * Class ilTestRandomQuestionSetConfigGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetConfigGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetConfigGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilAccess();
        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilLog();
        $this->addGlobal_tree();
        $this->addGlobal_ilComponentRepository();
        $this->addGlobal_objDefinition();
        $this->addGlobal_ilObjDataCache();

        $this->testObj = new ilTestRandomQuestionSetConfigGUI(
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock(),
            $DIC->ctrl(),
            $DIC->user(),
            $DIC->access(),
            $DIC->ui()->factory(),
            $DIC->ui()->renderer(),
            $DIC->tabs(),
            $DIC->language(),
            $DIC['ilLog'], // TODO: replace with proper attribute
            $DIC->ui()->mainTemplate(),
            $DIC->database(),
            $DIC['tree'], // TODO: replace with proper attribute
            $DIC['component.repository'], // TODO: replace with proper attribute
            $DIC['objDefinition'], // TODO: replace with proper attribute
            $DIC['ilObjDataCache'], // TODO: replace with proper attribute
            $this->getMockBuilder(ilTestProcessLockerFactory::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ILIAS\Test\InternalRequestService::class),
            $this->createMock(\ILIAS\TestQuestionPool\QuestionInfoService::class),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetConfigGUI::class, $this->testObj);
    }
}
