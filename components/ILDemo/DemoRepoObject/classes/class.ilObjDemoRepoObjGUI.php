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
 * @ilCtrl_isCalledBy ilObjDemoRepoObjGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjDemoRepoObjGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 */
class ilObjDemoRepoObjGUI extends ilObjectPluginGUI
{
    public const CMD_SHOW_CONTENT = "showContent";
    public const TAB_SHOW_CONTENT = "show_content";

    protected function afterConstructor(): void
    {
    }

    final public function getType(): string
    {
        return ilObjDemoRepoObj::TYPE;
    }

    protected function performNextClass(string $next_class): bool
    {
        switch ($next_class) {
        }
        return false;
    }

    public function getAfterCreationCmd(): string
    {
        return self::CMD_SHOW_CONTENT;
    }

    public function getStandardCmd(): string
    {
        return self::CMD_SHOW_CONTENT;
    }

    public function performCommand(string $cmd): void
    {
        switch ($cmd) {
            case self::CMD_SHOW_CONTENT:
                $this->tabs->activateTab(self::TAB_SHOW_CONTENT);
                $content = $this->showContent();
                $this->tpl->setContent($content);
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    public function setTabs(): void
    {
        $this->addInfoTab();

        $this->tabs->addTab(
            self::TAB_SHOW_CONTENT,
            $this->txt(self::TAB_SHOW_CONTENT),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_CONTENT)
        );

        $this->addPermissionTab();
    }

    protected function showContent()
    {
        $content = $this->txt('demo_view');
        $template = $this->plugin->getTemplate("tpl.demo.html", true, true);
        $template->setVariable('CONTENT', $content);
        return $template->get();
    }
}
