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
 * Class ilTestQuestionNavigationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionNavigationGUITest extends ilTestBaseTestCase
{
    use UITestHelper;

    private ilTestQuestionNavigationGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $ui_factory = $this->factory();
        $ui_renderer = $this->renderer();

        $this->testObj = new ilTestQuestionNavigationGUI(
            $this->createMock(ilLanguage::class),
            $ui_factory,
            $ui_renderer
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionNavigationGUI::class, $this->testObj);
    }
}
