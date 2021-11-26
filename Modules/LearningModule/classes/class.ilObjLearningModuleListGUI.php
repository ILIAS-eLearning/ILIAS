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

use ILIAS\LearningModule\Presentation\PresentationGUIRequest;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjLearningModuleListGUI extends ilObjectListGUI
{
    protected PresentationGUIRequest $request;
    private int $child_id = 0;

    public function init()
    {
        global $DIC;

        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "lm";
        $this->gui_class_name = "ilobjlearningmodulegui";

        $this->request = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->presentation()
            ->request();

        // general commands array
        $this->commands = ilObjLearningModuleAccess::_getCommands();
    }

    public function setChildId(int $a_child_id) : void
    {
        $this->child_id = $a_child_id;
    }

    public function getChildId() : int
    {
        return $this->child_id;
    }

    public function getCommandLink($a_cmd)
    {
        $ilCtrl = $this->ctrl;
        
        switch ($a_cmd) {
            case "continue":
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id .
                    "&amp;cmd=resume";
                break;

            case "page":
                // Used for presentation of single pages chapters in search results
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id .
                    "&amp;obj_id=" . $this->getChildId();
                break;

            case "view":
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id;
                break;

            case "edit":
                $cmd_link = "ilias.php?baseClass=ilLMEditorGUI&amp;ref_id=" . $this->ref_id;
                break;
                
            case "properties":
                $cmd_link = "ilias.php?baseClass=ilLMEditorGUI&amp;ref_id=" . $this->ref_id . "&amp;to_props=1";
                break;
                
            case "infoScreen":
                $cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=" . $this->ref_id .
                    "&amp;cmd=infoScreen&amp;file_id" . $this->getChildId();
                break;
                
            case 'downloadFile':
                $cmd_link = 'ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=' . $this->ref_id .
                    '&amp;cmd=downloadFile&amp;file_id=' . $this->getChildId();
                break;

            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->request->getRefId());
                break;
        }

        return $cmd_link;
    }

    public function getCommandFrame($a_cmd)
    {
        switch ($a_cmd) {
            case "view":
            case "continue":
            case "properties":
            case "infoScreen":
            case "edit":
            case 'list':
                $frame = ilFrameTargetInfo::_getFrame("MainContent");
                break;

            default:
                $frame = "";
                break;
        }

        return $frame;
    }

    public function getProperties()
    {
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;

        $props = parent::getProperties();

        if ($rbacsystem->checkAccess('write', $this->ref_id)) {
            $props[] = array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("lm"));
        }

        return $props;
    }

    public function getCommandImage($a_cmd)
    {
        switch ($a_cmd) {
            default:
                return "";
        }
    }
}
