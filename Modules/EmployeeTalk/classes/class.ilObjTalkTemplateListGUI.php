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

final class ilObjTalkTemplateListGUI extends ilObjectListGUI
{
    /**
     * initialisation
     */
    public function init(): void
    {
        parent::init();

        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->info_screen_enabled = true;
        $this->copy_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;

        $this->type = "talt";
        $this->gui_class_name = strtolower(ilObjTalkTemplateGUI::class);
        $this->commands = ilObjTalkTemplateAccess::_getCommands();
    }

    /**
     * no timing commands needed in orgunits.
     */
    public function insertTimingsCommand(): void
    {
    }

    /**
     * no social commands needed in orgunits.
     * @param bool $header_actions
     */
    public function insertCommonSocialCommands(bool $header_actions = false): void
    {
    }

    /**
     * @param string $cmd
     * @return string
     * @throws ilCtrlException
     */
    public function getCommandLink(string $cmd): string
    {
        $this->ctrl->setParameterByClass(strtolower(ilObjTalkTemplateGUI::class), "ref_id", $this->ref_id);
        return $this->ctrl->getLinkTargetByClass(strtolower(ilObjTalkTemplateGUI::class), $cmd);
    }
}
