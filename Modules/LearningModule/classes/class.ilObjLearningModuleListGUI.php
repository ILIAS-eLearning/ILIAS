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

use ILIAS\LearningModule\Presentation\PresentationGUIRequest;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjLearningModuleListGUI extends ilObjectListGUI
{
    protected PresentationGUIRequest $request;
    private int $child_id = 0;
    protected \ILIAS\LearningModule\ReadingTime\ReadingTimeManager $reading_time_manager;

    public function init() : void
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
        $this->enableLearningProgress(true);
        $this->lng->loadLanguageModule("copg");

        // general commands array
        $this->commands = ilObjLearningModuleAccess::_getCommands();
        $this->reading_time_manager = new \ILIAS\LearningModule\ReadingTime\ReadingTimeManager();
    }

    public function setChildId(int $a_child_id) : void
    {
        $this->child_id = $a_child_id;
    }

    public function getChildId() : int
    {
        return $this->child_id;
    }
    
    public function getCommandLink(string $cmd) : string
    {
        $ctrl = $this->ctrl;
        
        switch ($cmd) {
            case "continue":
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "ref_id", $this->ref_id);
                $cmd_link = $ctrl->getLinkTargetByClass(ilLMPresentationGUI::class, "resume");
                break;

            case "page":
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "ref_id", $this->ref_id);
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "obj_id", $this->getChildId());
                $cmd_link = $ctrl->getLinkTargetByClass(ilLMPresentationGUI::class, "");
                break;

            case "view":
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "ref_id", $this->ref_id);
                $cmd_link = $ctrl->getLinkTargetByClass(ilLMPresentationGUI::class, "");
                break;

            case "learningProgress":
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "ref_id", $this->ref_id);
                $cmd_link = $ctrl->getLinkTargetByClass(
                    [ilLMPresentationGUI::class, ilLearningProgressGUI::class   ],
                    "editManual"
                );
                break;

            case "edit":
                $ctrl->setParameterByClass(ilObjLearningModuleGUI::class, "ref_id", $this->ref_id);
                $cmd_link = $ctrl->getLinkTargetByClass([ilLMEditorGUI::class, ilObjLearningModuleGUI::class], "");
                break;
                
            case "properties":
                $ctrl->setParameterByClass(ilObjLearningModuleGUI::class, "ref_id", $this->ref_id);
                $cmd_link = $ctrl->getLinkTargetByClass([ilLMEditorGUI::class, ilObjLearningModuleGUI::class], "properties");
                break;
                
            case "infoScreen":
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "ref_id", $this->ref_id);
                $cmd_link = $ctrl->getLinkTargetByClass(ilLMPresentationGUI::class, "infoScreen");
                break;
                
            case 'downloadFile':
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "ref_id", $this->ref_id);
                $ctrl->setParameterByClass(ilLMPresentationGUI::class, "file_id", $this->getChildId());
                $cmd_link = $ctrl->getLinkTargetByClass(ilLMPresentationGUI::class, "file_id");
                break;

            default:
                $ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ctrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->request->getRefId());
                break;
        }

        return $cmd_link;
    }
    
    public function getCommandFrame(string $cmd) : string
    {
        switch ($cmd) {
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

    public function getProperties() : array
    {
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;

        $props = parent::getProperties();

        if ($rbacsystem->checkAccess('write', $this->ref_id)) {
            $props[] = array("alert" => false, "property" => $lng->txt("type"),
                "value" => $lng->txt("lm"));
        }

        $est_reading_time = $this->reading_time_manager->getReadingTime($this->obj_id);
        if (!is_null($est_reading_time)) {
            $props[] = array(
                "alert" => false,
                "property" => $lng->txt("copg_est_reading_time"),
                "value" => sprintf($lng->txt("copg_x_minutes"), $est_reading_time)
            );
        }

        return $props;
    }
    
    public function getInfoScreenStatus() : bool
    {
        return ilObjContentObjectAccess::isInfoEnabled($this->obj_id);
    }

    public function checkInfoPageOnAsynchronousRendering() : bool
    {
        return true;
    }
}
