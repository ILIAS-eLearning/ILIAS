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

class ilObjDemoRepoObjListGUI extends ilObjectPluginListGUI
{
    public function initCommands(): array
    {
        return [
            [
                "permission" => "read",
                "cmd" => \ilObjDemoRepoObjGUI::CMD_SHOW_CONTENT,
                "txt" => "show",
                "default" => true
            ],
            [
                "permission" => "write",
                "cmd" => \ilObjDemoRepoObjGUI::CMD_SHOW_CONTENT,
                "txt" => $this->lng->txt("edit"),
                "default" => false
            ],
        ];
    }

    public function initType()
    {
        $this->setType(ilObjDemoRepoObj::TYPE);
    }

    /**
    * Get name of gui class handling the commands
    */
    public function getGuiClass(): string
    {
        return "ilObjDemoRepoObjGUI";
    }

    public function getProperties(): array
    {
        return array();
    }

    protected function initListActions(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
    }
}
