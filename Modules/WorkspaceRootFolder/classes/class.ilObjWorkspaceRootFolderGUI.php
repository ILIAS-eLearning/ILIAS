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

/**
 * Class ilObjWorkspaceRootFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjWorkspaceRootFolderGUI: ilCommonActionDispatcherGUI, ilObjectOwnershipManagementGUI
 */
class ilObjWorkspaceRootFolderGUI extends ilObjWorkspaceFolderGUI
{
    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
    }

    public function getType(): string
    {
        return "wsrt";
    }

    protected function setTabs(bool $a_show_settings = false): void
    {
        $ilHelp = $this->help;

        parent::setTabs(false);
        $ilHelp->setScreenIdComponent("wsrt");
    }

    protected function setTitleAndDescription(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        $title = $lng->txt("mm_personal_and_shared_r");
        $tpl->setTitle($title);
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_wsrt.svg"), $title);
        $tpl->setDescription($lng->txt("wsp_personal_resources_description"));
    }
}
