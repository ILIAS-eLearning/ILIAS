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

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

class ilForumSettingsGUITest extends TestCase
{
    private ?Container $previous_dic = null;

    private array $sub_tabs = [];

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->previous_dic;

        parent::tearDown();
    }

    public function testOmitsNewsSubTabWhenGlobalNewsIsOff(): void
    {
        $gui = $this->settingsGui('0');

        $gui->settingsTabs();

        $this->assertSame(
            ['general', 'cont_style'],
            $this->sub_tabs
        );
    }

    public function testKeepsNewsSubTabWhenGlobalNewsIsOn(): void
    {
        $gui = $this->settingsGui('1');

        $gui->settingsTabs();

        $this->assertSame(
            ['general', 'cont_news_settings', 'cont_style'],
            $this->sub_tabs
        );
    }

    private function settingsGui(string $block_activated_news): ilForumSettingsGUI
    {
        global $DIC;

        $this->sub_tabs = [];
        $this->previous_dic = is_object($DIC) ? clone $DIC : $DIC;

        $ctrl = $this->createMock(ilCtrlInterface::class);
        $ctrl->method('getLinkTarget')->willReturn('edit');
        $ctrl->method('getLinkTargetByClass')->willReturn('target');

        $language = $this->createMock(ilLanguage::class);
        $settings = $this->createMock(ilSetting::class);
        $settings->method('get')->willReturnCallback(
            static function (string $key) use ($block_activated_news): ?string {
                return match ($key) {
                    'forum_notification' => '0',
                    'block_activated_news' => $block_activated_news,
                    default => null,
                };
            }
        );

        $tabs = $this->createMock(ilTabsGUI::class);
        $tabs->method('addSubTabTarget')->willReturnCallback(
            function (string $id): void {
                $this->sub_tabs[] = $id;
            }
        );

        $DIC = new Container();
        $DIC['ilCtrl'] = $ctrl;
        $DIC['lng'] = $language;
        $DIC['ilSetting'] = $settings;
        $DIC['ilTabs'] = $tabs;
        $DIC['ilAccess'] = $this->createMock(ilAccessHandler::class);
        $DIC['http'] = $this->createMock(\ILIAS\HTTP\Services::class);
        $DIC['tpl'] = $this->createMock(ilGlobalTemplateInterface::class);
        $DIC['ui.factory'] = $this->createMock(\ILIAS\UI\Factory::class);
        $DIC['ilErr'] = $this->createMock(ilErrorHandling::class);

        return new ilForumSettingsGUI(
            $this->createMock(ilObjForumGUI::class),
            $this->createMock(ilObjForum::class)
        );
    }
}
