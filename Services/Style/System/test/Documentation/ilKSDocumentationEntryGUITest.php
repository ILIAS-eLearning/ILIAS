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
use ILIAS\UI\Implementation\Component\Panel\Report;

class ilKSDocumentationEntryGUITest extends TestCase
{
    protected ilKSDocumentationEntryGUI $entry_gui;

    protected function setUp(): void
    {
        $ui_helper = new UITestHelper();

        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            'setParameterByClass'
        ])->getMock();

        $entries_data = include './tests/UI/Crawler/Fixture/EntriesFixture.php';
        $entries = new Entries();
        $entries->addEntriesFromArray($entries_data);

        $this->entry_gui = new ilKSDocumentationEntryGUI(
            $ui_helper->factory(),
            $ctrl,
            $entries,
            'Entry1'
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilKSDocumentationEntryGUI::class, $this->entry_gui);
    }

    public function testRenderEntry(): void
    {
        $report = $this->entry_gui->createUIComponentOfEntry();
        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals('Entry1Title', $report->getTitle());
    }
}
