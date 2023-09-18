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
 * Class ilObjGlossaryListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjGlossaryListGUI extends ilObjectListGUI
{
    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "glo";
        $this->gui_class_name = "ilobjglossarygui";

        // general commands array
        $this->commands = ilObjGlossaryAccess::_getCommands();
    }

    public function getCommandLink(string $cmd): string
    {
        switch ($cmd) {
            case "view":
                $cmd_link = "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "edit":
                $cmd_link = "ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "properties":
                $this->ctrl->setParameterByClass("ilobjglossarygui", "ref_id", $this->ref_id);
                $cmd_link = $this->ctrl->getLinkTargetByClass(array("ilglossaryeditorgui", "ilobjglossarygui"), $cmd);
                break;

            case "infoScreen":
                $cmd_link = "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=infoScreen&amp;ref_id=" . $this->ref_id;
                break;

            default:
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->requested_ref_id);
                break;
        }

        return $cmd_link;
    }

    public function getCommandFrame(string $cmd): string
    {
        switch ($cmd) {
            case "view":
            case "edit":
            case "properties":
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
                $frame = "";
                break;
        }

        return $frame;
    }


    public function getProperties(): array
    {
        $lng = $this->lng;
        $props = array();
        if (!ilObjGlossaryAccess::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props;
    }
}
