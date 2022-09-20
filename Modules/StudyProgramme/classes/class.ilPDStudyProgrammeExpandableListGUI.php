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

/**
 * Personal Desktop-Presentation for the Study Programme
 *
 * @ilCtrl_IsCalledBy ilPDStudyProgrammeExpandableListGUI: ilColumnGUI
 */
class ilPDStudyProgrammeExpandableListGUI extends ilPDStudyProgrammeSimpleListGUI
{
    public const BLOCK_TYPE = "prgexpandablelist";

    protected function shouldShowThisList(): bool
    {
        $cmd = $this->request_wrapper->retrieve("cmd", $this->refinery->kindlyTo()->string());
        $expand = $this->request_wrapper->retrieve("expand", $this->refinery->kindlyTo()->bool());
        return $cmd === "jumpToSelectedItems" && $expand;
    }

    protected function new_ilStudyProgrammeAssignmentListGUI(
        ilStudyProgrammeAssignment $assignment
    ): ilStudyProgrammeExpandableProgressListGUI {
        $prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
        $progress = $prg->getProgressForAssignment($assignment->getId());
        $progress_gui = new ilStudyProgrammeExpandableProgressListGUI($progress);
        $progress_gui->setOnlyRelevant(true);
        return $progress_gui;
    }
}
