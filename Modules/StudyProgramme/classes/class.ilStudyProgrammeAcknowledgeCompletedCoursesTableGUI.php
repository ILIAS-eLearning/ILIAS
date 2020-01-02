<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for acknowledgement of completed courses for new members
* of a study programme.
*
* @author	Richard Klees
* @version	$Id$
*/
class ilStudyProgrammeAcknowledgeCompletedCoursesTableGUI extends ilTable2GUI
{
    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_user_id, $a_completed_courses)
    {
        $this->folder = $a_folder;

        parent::__construct($a_parent_obj, "");
        $this->user_id = $a_user_id;

        $this->addColumn("", "", "1", 1);
        $this->addColumn($this->lng->txt("title"));

        $this->setRowTemplate("tpl.acknowledge_completed_courses_row.html", "Modules/StudyProgramme");

        $this->setData($a_completed_courses);
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable("USR_ID", $this->user_id);
        $this->tpl->setVariable("PRG_REF_ID", $a_set["prg_ref_id"]);
        $this->tpl->setVariable("CRS_ID", $a_set["crs_id"]);
        $this->tpl->setVariable("CRSR_ID", $a_set["crsr_id"]);
        $this->tpl->setVariable("CRS_TITLE", $a_set["title"]);
    }
}
