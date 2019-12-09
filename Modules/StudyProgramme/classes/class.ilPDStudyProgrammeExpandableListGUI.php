<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Modules/StudyProgramme/classes/class.ilPDStudyProgrammeSimpleListGUI.php");

/**
 * Personal Desktop-Presentation for the Study Programme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 * @ilCtrl_IsCalledBy ilPDStudyProgrammeExpandableListGUI: ilColumnGUI
 */
class ilPDStudyProgrammeExpandableListGUI extends ilPDStudyProgrammeSimpleListGUI
{
    const BLOCK_TYPE = "prgexpandablelist";

    public function __construct()
    {
        parent::__construct();
    }

    protected function shouldShowThisList()
    {
        return $_GET["cmd"] == "jumpToSelectedItems" &&  $_GET["expand"];
    }

    protected function new_ilStudyProgrammeAssignmentListGUI(ilStudyProgrammeUserAssignment $a_assignment)
    {
        require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeExpandableProgressListGUI.php");
        $progress = $a_assignment->getStudyProgramme()->getProgressForAssignment($a_assignment->getId());
        $progress_gui = new ilStudyProgrammeExpandableProgressListGUI($progress);
        $progress_gui->setOnlyRelevant(true);
        return $progress_gui;
    }
}
