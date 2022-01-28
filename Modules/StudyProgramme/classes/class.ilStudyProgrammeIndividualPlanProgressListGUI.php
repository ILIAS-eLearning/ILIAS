<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

class ilStudyProgrammeIndividualPlanProgressListGUI extends ilStudyProgrammeExpandableProgressListGUI
{
    protected function showMyProgress() : bool
    {
        // expand tree completely on start
        return $this->progress->isRelevant();
    }
    
    public function shouldShowSubProgress(ilStudyProgrammeProgress $progress) : bool
    {
        return true;
    }
    
    public function newSubItem(ilStudyProgrammeProgress $progress) : ilStudyProgrammeExpandableProgressListGUI
    {
        return new ilStudyProgrammeIndividualPlanProgressListGUI($progress);
    }
    
    protected function getTitleForItem(ilObjStudyProgramme $programme) : string
    {
        $title = $programme->getTitle();
        if (!$this->progress->isRelevant()
            || $programme->getStatus() == ilStudyProgrammeSettings::STATUS_OUTDATED
        ) {
            return "<s>" . $title . "</s>";
        }
        return $title;
    }
    
    protected function buildProgressStatus(ilStudyProgrammeProgress $progress) : string
    {
        $programme = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());
        $can_be_completed = $programme->canBeCompleted($progress);
        
        $points = parent::buildProgressStatus($progress);
        if (!$can_be_completed && !$progress->isSuccessful()) {
            return
                "<img src='" .
                ilUtil::getImagePath("icon_alert.svg") .
                "' alt='" .
                $this->il_lng->txt("warning") .
                "'>" .
                $points
            ;
        } else {
            return $points;
        }
    }
    
    protected function configureItemGUI(ilStudyProgrammeCourseListGUI $item_gui) : void
    {
        $item_gui->enableComments(false);
        $item_gui->enableTags(false);
        $item_gui->enableIcon(true);
        $item_gui->enableDelete(false);
        $item_gui->enableCut(false);
        $item_gui->enableCopy(false);
        $item_gui->enableLink(false);
        $item_gui->enableInfoScreen(false);
        $item_gui->enableSubscribe(false);
        $item_gui->enableCheckbox(false);
        $item_gui->enableDescription(true);
        $item_gui->enableProperties(false);
        $item_gui->enablePreconditions(false);
        $item_gui->enableNoticeProperties(false);
        $item_gui->enableCommands(false, true);
        $item_gui->enableProgressInfo(false);
        $item_gui->setIndent($this->getIndent() + 2);
    }
}
