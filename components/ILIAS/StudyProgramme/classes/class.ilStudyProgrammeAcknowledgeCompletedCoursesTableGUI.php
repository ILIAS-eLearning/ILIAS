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

declare(strict_types=1);

/**
* TableGUI class for acknowledgement of completed courses for new members
* of a study programme.
*
* @author	Richard Klees
* @version	$Id$
*/
class ilStudyProgrammeAcknowledgeCompletedCoursesTableGUI extends ilTable2GUI
{
    protected int $assignmet_id;

    public function __construct(
        ilObjStudyProgrammeMembersGUI $parent_obj,
        ilPRGAssignment $assignment,
        array $completed_courses
    ) {
        parent::__construct($parent_obj);
        $this->assignmet_id = $assignment->getId();

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("title"));
        $this->setRowTemplate("tpl.acknowledge_completed_courses_row.html", "Modules/StudyProgramme");
        $this->setData($completed_courses);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('CRS_TITLE', $a_set['title']);
        $this->tpl->setVariable('ASSIGNMENT_ID', $this->assignmet_id);
        $this->tpl->setVariable('PRG_NODE_ID', $a_set['prg_obj_id']);
        $this->tpl->setVariable('COURSEREFERENCE_OBJ_ID', $a_set['crsr_id']);
    }
}
