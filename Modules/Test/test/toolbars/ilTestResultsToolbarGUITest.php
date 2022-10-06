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
 * Class ilTestResultsToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestResultsToolbarGUI $toolbarGUI;

    protected function setUp(): void
    {
        parent::setUp();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $mainTpl_mock = $this->createMock(ilGlobalPageTemplate::class);

        $this->setGlobalVariable("lng", $lng_mock);

        $this->toolbarGUI = new ilTestResultsToolbarGUI(
            $ctrl_mock,
            $mainTpl_mock,
            $lng_mock,
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultsToolbarGUI::class, $this->toolbarGUI);
    }
}
