<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgrammeProgressListGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 */
require_once('./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeAdmin.php');
class ilStudyProgrammeProgressListGUI
{
    protected static $tpl_file = "tpl.progress_list_item.html";

    const SUCCESSFUL_PROGRESS_CSS_CLASS = "ilCourseObjectiveProgressBarCompleted";
    const NON_SUCCESSFUL_PROGRESS_CSS_CLASS = "ilCourseObjectiveProgressBarNeutral";

    /**
     * @var ilLanguage
     */
    protected $il_lng;
    
    /**
     * @var ilCtrl
     */
    protected $il_ctrl;
    
    /**
     * @var ilStudyProgrammeUserProgress
     */
    protected $progress;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var string
     */
    protected $show_info_mesage;

    /**
     * @var string
     */
    protected $visible_on_pd_mode;

    /**
     * @var boolean
     */
    protected $only_relevant = false;

    public function __construct(ilStudyProgrammeUserProgress $a_progress)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $this->il_lng = $lng;
        $this->il_lng->loadLanguageModule("prg");
        $this->il_ctrl = $ilCtrl;
        
        $this->progress = $a_progress;
        $this->tpl = null;
        $this->html = null;
        $this->show_info_mesage = false;
        $this->visible_on_pd_mode = "read";
    }
    
    public function getHTML()
    {
        if ($this->html === null) {
            $tpl = $this->getTemplate("Modules/StudyProgramme", static::$tpl_file, true, true);
            $this->fillTemplate($tpl);
            $this->html = $tpl->get();
        }
        return $this->html;
    }
    
    protected function fillTemplate($tpl)
    {
        $programme = $this->progress->getStudyProgramme();
        $title_and_icon_target = $this->getTitleAndIconTarget($this->progress);
        
        if ($title_and_icon_target) {
            $tpl->setCurrentBlock("linked_icon");
            $tpl->setVariable("SRC_ICON", $this->getIconPath($programme->getId()));
            $tpl->setVariable("ALT_ICON", $this->getAltIcon($programme->getId()));
            $tpl->setVariable("ICON_HREF", $title_and_icon_target);
            $tpl->parseCurrentBlock();
            
            $tpl->setCurrentBlock("linked_title");
            $tpl->setVariable("TXT_TITLE", $this->getTitleForItem($programme));
            $tpl->setVariable("HREF_TITLE", $title_and_icon_target);
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setCurrentBlock("not_linked_icon");
            $tpl->setVariable("SRC_ICON", $this->getIconPath($programme->getId()));
            $tpl->setVariable("ALT_ICON", $this->getAltIcon($programme->getId()));
            $tpl->parseCurrentBlock();
            
            $tpl->setCurrentBlock("not_linked_title");
            $tpl->setVariable("TXT_TITLE", $this->getTitleForItem($programme));
            $tpl->parseCurrentBlock();
        }
        
        if ($this->show_info_mesage) {
            if ($this->showMoreObjectsInfo($programme)) {
                $tpl->setVariable("MORE_OBJECTS", $this->il_lng->txt("prg_more_objects_without_read_permission"));
            }
        }
        $tpl->setVariable("TXT_DESC", $programme->getDescription());
        $tpl->setVariable("PROGRESS_BAR", $this->buildProgressBar($this->progress));
    }
    
    protected function getTitleForItem(ilObjStudyProgramme $a_programme)
    {
        return $a_programme->getTitle();
    }
    
    protected function getTemplate($a_component, $a_file, $a_remove_unknown_vars, $a_remove_empty_blocks)
    {
        return new ilTemplate($a_file, $a_remove_unknown_vars, $a_remove_empty_blocks, $a_component);
    }
    
    protected function getIconPath($a_obj_id)
    {
        return ilObject::_getIcon($a_obj_id, "small", "prg");
    }
    
    protected function getAltIcon($a_obj_id)
    {
        return $this->il_lng->txt("icon") . " " . $this->il_lng->txt("obj_prg");
    }
    
    protected function getTitleAndIconTarget(ilStudyProgrammeUserProgress $a_progress)
    {
        $this->il_ctrl->setParameterByClass("ilPersonalDesktopGUI", "prg_progress_id", $a_progress->getId());
        $this->il_ctrl->setParameterByClass("ilPersonalDesktopGUI", "expand", 1);
        $link = $this->il_ctrl->getLinkTargetByClass("ilPersonalDesktopGUI", "jumpToSelectedItems");
        $this->il_ctrl->setParameterByClass("ilPersonalDesktopGUI", "prg_progress_id", null);
        $this->il_ctrl->setParameterByClass("ilPersonalDesktopGUI", "expand", null);
        return $link;
    }
    
    protected function buildProgressBar(ilStudyProgrammeUserProgress $a_progress)
    {
        $tooltip_id = "prg_" . $a_progress->getId();
        
        $required_amount_of_points = $a_progress->getAmountOfPoints();
        $maximum_possible_amount_of_points = $a_progress->getMaximumPossibleAmountOfPoints(true);
        $current_amount_of_points = $a_progress->getCurrentAmountOfPoints();

        if ($maximum_possible_amount_of_points > 0) {
            $current_percent = (int) ($current_amount_of_points * 100 / $maximum_possible_amount_of_points);
            $required_percent = (int) ($required_amount_of_points * 100 / $maximum_possible_amount_of_points);
        } else {
            if ($a_progress->isSuccessful()) {
                $current_percent = 100;
                $required_percent = 100;
            } else {
                $current_percent = 0;
                $required_percent = 0;
            }
        }
        
        //required to dodge bug in ilContainerObjectiveGUI::renderProgressBar
        if ($required_percent == 0) {
            $required_percent = 0.1;
        }
        
        $tooltip_txt = $this->buildToolTip($a_progress);
        $progress_status = $this->buildProgressStatus($a_progress);
        
        if ($a_progress->isSuccessful()) {
            $css_class = self::SUCCESSFUL_PROGRESS_CSS_CLASS;
        } else {
            $css_class = self::NON_SUCCESSFUL_PROGRESS_CSS_CLASS;
        }

        require_once("Services/Container/classes/class.ilContainerObjectiveGUI.php");
        return ilContainerObjectiveGUI::renderProgressBar($current_percent, $required_percent, $css_class, $progress_status, null, $tooltip_id, $tooltip_txt);
    }
    
    protected function buildToolTip(ilStudyProgrammeUserProgress $a_progress)
    {
        return sprintf(
            $this->il_lng->txt("prg_progress_info"),
            $a_progress->getCurrentAmountOfPoints(),
            $a_progress->getAmountOfPoints()
        );
    }
    
    protected function buildProgressStatus(ilStudyProgrammeUserProgress $a_progress)
    {
        $lang_val = "prg_progress_status";
        $max_points = $a_progress->getAmountOfPoints();
        $study_programm = $a_progress->getStudyProgramme();

        if ($study_programm->hasChildren() && !$study_programm->hasLPChildren()) {
            $lang_val = "prg_progress_status_with_child_sp";
        }

        if ($a_progress->getStudyProgramme()->hasChildren()) {
            $max_points = $a_progress->getMaximumPossibleAmountOfPoints($this->only_relevant);
        }
        return sprintf(
            $this->il_lng->txt($lang_val),
            $a_progress->getCurrentAmountOfPoints(),
            $max_points
        );
    }

    public function setShowInfoMessage($show_info_mesage)
    {
        $this->show_info_mesage = $show_info_mesage;
    }

    public function setVisibleOnPDMode($visible_on_pd_mode)
    {
        $this->visible_on_pd_mode = $visible_on_pd_mode;
    }

    public function setOnlyRelevant($only_relevant)
    {
        $this->only_relevant = $only_relevant;
    }

    protected function showMoreObjectsInfo($programme)
    {
        $children = $programme->getChildren();
        foreach ($children as $key => $child) {
            $read = $this->il_access->checkAccess("read", "", $child->getRefId(), "prg", $child->getId());
            if (!$read && $this->visible_on_pd_mode != ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
                return true;
            }
        }

        return false;
    }
}
