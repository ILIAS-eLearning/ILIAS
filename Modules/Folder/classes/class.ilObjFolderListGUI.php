<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilObjFolderListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjFolderListGUI extends ilObjectListGUI
{
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "fold";
        $this->gui_class_name = "ilobjfoldergui";

        // general commands array
        $this->commands = ilObjFolderAccess::_getCommands();
    }

    public function getCommandLink($a_cmd)
    {
        $ilCtrl = $this->ctrl;
        
        // BEGIN WebDAV: Mount webfolder.
        switch ($a_cmd) {
            default:

                if ($a_cmd == 'mount_webfolder' && ilDAVActivationChecker::_isActive()) {
                    global $DIC;
                    $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
                    $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
                    $cmd_link = $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
                    break;
                }

                // separate method for this line
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                break;
        }
        
        return $cmd_link;
    }
}
