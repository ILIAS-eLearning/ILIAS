<?php declare(strict_types=1);

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
 * Class ilStudyProgrammeProgressListGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 * @author: Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilStudyProgrammeProgressListGUI
{
    private const SUCCESSFUL_PROGRESS_CSS_CLASS = "ilCourseObjectiveProgressBarCompleted";
    private const NON_SUCCESSFUL_PROGRESS_CSS_CLASS = "ilCourseObjectiveProgressBarNeutral";

    protected static string $tpl_file = "tpl.progress_list_item.html";

    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilAccess $access;
    protected ilStudyProgrammeProgress $progress;
    protected ?ilGlobalTemplateInterface $tpl;
    protected ?string $html;
    protected bool $show_info_message;
    protected string $visible_on_pd_mode;
    protected bool $only_relevant = false;

    public function __construct(ilStudyProgrammeProgress $a_progress)
    {
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule("prg");
        $this->ctrl = $DIC['ilCtrl'];
        $this->access = $DIC['ilAccess'];
        
        $this->progress = $a_progress;
        $this->tpl = null;
        $this->html = null;
        $this->show_info_message = false;
        $this->visible_on_pd_mode = "read";
    }
    
    public function getHTML() : string
    {
        if ($this->html === null) {
            $tpl = $this->getTemplate("Modules/StudyProgramme", static::$tpl_file, true, true);
            $this->fillTemplate($tpl);
            $this->html = $tpl->get();
        }
        return $this->html;
    }
    
    protected function fillTemplate(ilTemplate $tpl) : void
    {
        $programme = ilObjStudyProgramme::getInstanceByObjId($this->progress->getNodeId());
        $title_and_icon_target = $this->getTitleAndIconTarget($this->progress);
        
        if ($title_and_icon_target) {
            $tpl->setCurrentBlock("linked_icon");
            $tpl->setVariable("SRC_ICON", $this->getIconPath($programme->getId()));
            $tpl->setVariable("ALT_ICON", $this->getAltIcon());
            $tpl->setVariable("ICON_HREF", $title_and_icon_target);
            $tpl->parseCurrentBlock();
            
            $tpl->setCurrentBlock("linked_title");
            $tpl->setVariable("TXT_TITLE", $this->getTitleForItem($programme));
            $tpl->setVariable("HREF_TITLE", $title_and_icon_target);
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setCurrentBlock("not_linked_icon");
            $tpl->setVariable("SRC_ICON", $this->getIconPath($programme->getId()));
            $tpl->setVariable("ALT_ICON", $this->getAltIcon());
            $tpl->parseCurrentBlock();
            
            $tpl->setCurrentBlock("not_linked_title");
            $tpl->setVariable("TXT_TITLE", $this->getTitleForItem($programme));
            $tpl->parseCurrentBlock();
        }

        if ($this->show_info_message && $this->showMoreObjectsInfo($programme)) {
            $tpl->setVariable("MORE_OBJECTS", $this->lng->txt("prg_more_objects_without_read_permission"));
        }
        $tpl->setVariable("TXT_DESC", $programme->getDescription());
        $tpl->setVariable("PROGRESS_BAR", $this->buildProgressBar($this->progress));
    }
    
    protected function getTitleForItem(ilObjStudyProgramme $programme) : string
    {
        return $programme->getTitle();
    }
    
    protected function getTemplate(
        string $component,
        string $file,
        bool $remove_unknown_vars,
        bool $remove_empty_blocks
    ) : ilTemplate {
        return new ilTemplate($file, $remove_unknown_vars, $remove_empty_blocks, $component);
    }

    protected function getIconPath(int $obj_id) : string
    {
        return ilObject::_getIcon($obj_id, "small", "prg");
    }
    
    protected function getAltIcon() : string
    {
        return $this->lng->txt("icon") . " " . $this->lng->txt("obj_prg");
    }
    
    protected function getTitleAndIconTarget(ilStudyProgrammeProgress $progress) : ?string
    {
        $this->ctrl->setParameterByClass("ilDashboardGUI", "prg_progress_id", $progress->getId());
        $this->ctrl->setParameterByClass("ilDashboardGUI", "expand", 1);
        $link = $this->ctrl->getLinkTargetByClass("ilDashboardGUI", "jumpToSelectedItems");
        $this->ctrl->setParameterByClass("ilDashboardGUI", "prg_progress_id", null);
        $this->ctrl->setParameterByClass("ilDashboardGUI", "expand", null);
        return $link;
    }
    
    protected function buildProgressBar(ilStudyProgrammeProgress $progress) : string
    {
        $tooltip_id = "prg_" . $progress->getId();
        $required_amount_of_points = $progress->getAmountOfPoints();

        $programme = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());
        $maximum_possible_amount_of_points = $programme->getPossiblePointsOfRelevantChildren($progress);
        
        $current_amount_of_points = $progress->getCurrentAmountOfPoints();
        $current_percent = 0;
        $required_percent = 0;

        if ($maximum_possible_amount_of_points > 0) {
            $current_percent = (int) ($current_amount_of_points * 100 / $maximum_possible_amount_of_points);
            $required_percent = (int) ($required_amount_of_points * 100 / $maximum_possible_amount_of_points);
        } elseif ($progress->isSuccessful()) {
            $current_percent = 100;
            $required_percent = 100;
        }
        
        //required to dodge bug in ilContainerObjectiveGUI::renderProgressBar
        if ($required_percent === 0) {
            $required_percent = 0.1;
        }
        
        $tooltip_txt = $this->buildToolTip($progress);
        $progress_status = $this->buildProgressStatus($progress);
        
        if ($progress->isSuccessful()) {
            $css_class = self::SUCCESSFUL_PROGRESS_CSS_CLASS;
        } else {
            $css_class = self::NON_SUCCESSFUL_PROGRESS_CSS_CLASS;
        }

        return ilContainerObjectiveGUI::renderProgressBar(
            $current_percent,
            $required_percent,
            $css_class,
            $progress_status,
            null,
            $tooltip_id,
            $tooltip_txt
        );
    }
    
    protected function buildToolTip(ilStudyProgrammeProgress $progress) : string
    {
        return sprintf(
            $this->lng->txt("prg_progress_info"),
            $progress->getCurrentAmountOfPoints(),
            $progress->getAmountOfPoints()
        );
    }
    
    protected function buildProgressStatus(ilStudyProgrammeProgress $progress) : string
    {
        $lang_val = "prg_progress_status";
        $max_points = $progress->getAmountOfPoints();
        $programme = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());

        if ($programme->hasChildren() && !$programme->hasLPChildren()) {
            $lang_val = "prg_progress_status_with_child_sp";
        }

        if ($programme->hasChildren()) {
            $max_points = $programme->getPossiblePointsOfRelevantChildren($progress);
        }

        return sprintf(
            $this->lng->txt($lang_val),
            $progress->getCurrentAmountOfPoints(),
            $max_points
        );
    }

    public function setShowInfoMessage(bool $show_info_message) : void
    {
        $this->show_info_message = $show_info_message;
    }

    public function setVisibleOnPDMode(string $visible_on_pd_mode) : void
    {
        $this->visible_on_pd_mode = $visible_on_pd_mode;
    }

    public function setOnlyRelevant(bool $only_relevant) : void
    {
        $this->only_relevant = $only_relevant;
    }

    protected function showMoreObjectsInfo(ilObjStudyProgramme $programme) : bool
    {
        $children = $programme->getChildren();
        foreach ($children as $child) {
            $read = $this->access->checkAccess("read", "", $child->getRefId(), "prg", $child->getId());
            if (!$read && $this->visible_on_pd_mode !== ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS) {
                return true;
            }
        }

        return false;
    }
}
