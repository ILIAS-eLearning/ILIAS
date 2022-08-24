<?php

declare(strict_types=1);

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

class ilStudyProgrammeIndividualPlanProgressListGUI extends ilStudyProgrammeExpandableProgressListGUI
{
    protected function showMyProgress(): bool
    {
        // expand tree completely on start
        return $this->progress->isRelevant();
    }

    protected function shouldShowSubProgress(ilStudyProgrammeProgress $progress): bool
    {
        return true;
    }

    protected function newSubItem(ilStudyProgrammeProgress $progress): ilStudyProgrammeExpandableProgressListGUI
    {
        return new ilStudyProgrammeIndividualPlanProgressListGUI($progress);
    }

    protected function getTitleForItem(ilObjStudyProgramme $programme): string
    {
        $title = $programme->getTitle();
        if (!$this->progress->isRelevant()
            || $programme->getStatus() === ilStudyProgrammeSettings::STATUS_OUTDATED
        ) {
            return "<s>" . $title . "</s>";
        }
        return $title;
    }

    protected function buildProgressStatus(ilStudyProgrammeProgress $progress): string
    {
        $programme = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());
        $can_be_completed = $programme->canBeCompleted($progress);

        $points = parent::buildProgressStatus($progress);
        if (!$can_be_completed && !$progress->isSuccessful()) {
            return
                "<img src='" .
                ilUtil::getImagePath("icon_alert.svg") .
                "' alt='" .
                $this->lng->txt("warning") .
                "'>" .
                $points
            ;
        }

        return $points;
    }

    protected function configureItemGUI(ilStudyProgrammeCourseListGUI $item_gui): void
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
