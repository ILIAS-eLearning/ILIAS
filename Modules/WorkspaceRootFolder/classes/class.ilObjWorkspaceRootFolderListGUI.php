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

use ILIAS\PersonalWorkspace\StandardGUIRequest;

/**
 * Class ilObjWorkspaceRootFolderListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjWorkspaceRootFolderListGUI extends ilObjectListGUI
{
    protected StandardGUIRequest $request;

    public function init() : void
    {
        global $DIC;

        $this->copy_enabled = false;
        $this->delete_enabled = false;
        $this->cut_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->type = "root";
        $this->gui_class_name = "ilobjworkspacerootfoldergui";
        $this->request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        // general commands array
        $this->commands = ilObjWorkspaceRootFolderAccess::_getCommands();
    }

    /**
     * Get command link url.
     */
    public function getCommandLink(string $cmd) : string
    {
        $ilCtrl = $this->ctrl;

        // does this make any sense!?
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->request->getRefId());

        return $cmd_link;
    }
}
