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
 * Class ilTalkTemplateListGUI
 *
 * @author            Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilTalkTemplateListGUI: ilEmployeeTalkGUI
 * @ilCtrl_Calls      ilTalkTemplateListGUI: ilFormPropertyDispatchGUI
 */
final class ilObjEmployeeTalkListGUI extends ilObjectListGUI
{
    /**
     * initialisation
     */
    public function init(): void
    {
        parent::init();

        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->info_screen_enabled = false;
        $this->copy_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;

        $this->type = "talt";
        $this->gui_class_name = strtolower(ilObjEmployeeTalkGUI::class);
        $this->commands = ilObjEmployeeTalkAccess::_getCommands();
    }

    /**
     * no timing commands needed in orgunits.
     */
    public function insertTimingsCommand(): void
    {
    }

    /**
     * no social commands needed in orgunits.
     * @param bool $a_header_actions
     */
    public function insertCommonSocialCommands($a_header_actions = false): void
    {
    }

    /**
     * @param string $a_cmd
     *
     * @return string
     */
    public function getCommandLink(string $a_cmd): string
    {
        $this->ctrl->setParameterByClass(strtolower(ilObjEmployeeTalkGUI::class), "ref_id", $this->ref_id);
        return $this->ctrl->getLinkTargetByClass(strtolower(ilObjEmployeeTalkGUI::class), $a_cmd);
    }
}
