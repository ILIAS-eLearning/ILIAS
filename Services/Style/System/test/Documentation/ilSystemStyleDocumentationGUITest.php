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

require_once('libs/composer/vendor/autoload.php');
include_once('./tests/UI/UITestHelper.php');

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;

class ilSystemStyleDocumentationGUITest extends TestCase
{
    protected ilSystemStyleDocumentationGUI $documentation_gui;
    protected ilGlobalPageTemplate $tpl_observer;

    protected function setUp() : void
    {
        $ui_helper = new UITestHelper();
        $this->tpl_observer = $this->getMockBuilder(ilGlobalPageTemplate::class)->disableOriginalConstructor()->getMock();
        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();

        $this->documentation_gui = new ilSystemStyleDocumentationGUI(
            $this->tpl_observer,
            $ctrl,
            $ui_helper->factory(),
            $ui_helper->renderer()
        );
    }

    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilSystemStyleDocumentationGUI::class, $this->documentation_gui);
    }

    public function testShow() : void
    {
        $entries_data = include './tests/UI/Crawler/Fixture/EntriesFixture.php';
        $entries = new Entries();
        $entries->addEntriesFromArray($entries_data);
        $this->tpl_observer->expects($this->once())
                           ->method('setContent')
                           ->with($this->stringContains('Entry1Title'));
        $this->documentation_gui->show($entries, 'Entry1');
    }
}
