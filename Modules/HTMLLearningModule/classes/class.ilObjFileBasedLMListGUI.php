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
 * Class ilObjFileBasedLMListGUI
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjFileBasedLMListGUI extends ilObjectListGUI
{
    public function init() : void
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "htlm";
        $this->gui_class_name = "ilobjfilebasedlmgui";
        $this->enableLearningProgress(true);
        
        // general commands array
        $this->commands = ilObjFileBasedLMAccess::_getCommands();
    }

    public function getCommandLink(string $cmd) : string
    {
        $ilCtrl = $this->ctrl;
        
        switch ($cmd) {
            case "view":
                $cmd_link = "ilias.php?baseClass=ilHTLMPresentationGUI&ref_id=" . $this->ref_id;
                break;

            case "edit":
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass(
                    ["ilrepositorygui", "ilObjFileBasedLMGUI", "ilFileSystemGUI"],
                    "listFiles"
                );
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                break;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->requested_ref_id);
                break;
        }

        return $cmd_link;
    }

    public function getCommandFrame(string $cmd) : string
    {
        switch ($cmd) {
            case "view":
                $frame = "ilContObj" . $this->obj_id;
                break;

            case "edit":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
                $frame = "";
                break;
        }

        return $frame;
    }

    public function getProperties() : array
    {
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;

        // centralized offline status
        $props = parent::getProperties();

        if (!ilObjFileBasedLMAccess::_determineStartUrl($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("no_start_file"));
        }

        if ($rbacsystem->checkAccess("write", $this->ref_id)) {
            $props[] = array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("htlm"));
        }

        return $props;
    }

    public function getInfoScreenStatus() : bool
    {
        return ilObjFileBasedLMAccess::isInfoEnabled($this->obj_id);
    }

    public function checkInfoPageOnAsynchronousRendering() : bool
    {
        return true;
    }
}
