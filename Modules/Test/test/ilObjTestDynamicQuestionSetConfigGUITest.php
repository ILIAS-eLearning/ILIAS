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
 * Class ilObjTestDynamicQuestionSetConfigGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestDynamicQuestionSetConfigGUITest extends ilTestBaseTestCase
{
    private ilObjTestDynamicQuestionSetConfigGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilObjTestDynamicQuestionSetConfigGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilDBInterface::class),
            $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilComponentRepository::class),
            $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock()
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilObjTestDynamicQuestionSetConfigGUI::class, $this->testObj);
    }
}
