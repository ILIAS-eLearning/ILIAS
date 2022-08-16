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

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilObjTestSettingsScoringResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class ilObjTestSettingsScoringResultsGUITest extends TestCase
{
    protected function getUIComponents(): array
    {
        $test_helper = new UITestHelper();

        $ui_factory = $test_helper->factory();
        $ui_renderer = $test_helper->renderer();
        $refinery = $this->getMockBuilder(\ILIAS\Refinery\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->createMock(ServerRequestInterface::class);

        $main_template = $test_helper->mainTemplate();
        $tabs_gui = $this->createMock(ilTabsGUI::class);

        return [
            $ui_factory,
            $ui_renderer,
            $refinery,
            $request,
            $main_template,
            $tabs_gui
        ];
    }


    public function testScoringResultsGUIConstruct(): void
    {
        $objTestGui_mock = $this->getMockBuilder(ilObjTestGUI::class)->disableOriginalConstructor()->onlyMethods(array('getObject'))->getMock();
        $objTestGui_mock->expects(
            $this->any()
        )->method('getObject')->willReturn(
            $this->createMock(ilObjTest::class)
        );

        list($ui_factory, $ui_renderer, $refinery, $request, $main_template, $tabs_gui) = $this->getUIComponents();

        $this->testObj = new ilObjTestSettingsScoringResultsGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ilTree::class)->disableOriginalConstructor()->getMock(),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class),
            $objTestGui_mock,
            $main_template,
            $tabs_gui,
            $this->createMock(ScoreSettingsRepository::class),
            -123,
            $ui_factory,
            $ui_renderer,
            $refinery,
            $request
        );

        $this->assertInstanceOf(ilObjTestSettingsScoringResultsGUI::class, $this->testObj);
    }
}
