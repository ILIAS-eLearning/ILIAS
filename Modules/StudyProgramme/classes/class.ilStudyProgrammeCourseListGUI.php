<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgrammeCourseListGUI
 *
 * ilObjCourseListGUI with possibility for identations, used for study programme view on personal desktop.
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

require_once("Modules/Course/classes/class.ilObjCourseListGUI.php");

class ilStudyProgrammeCourseListGUI extends ilObjCourseListGUI
{
    protected static $tpl_file_name = "tpl.course_list_item.html";
    protected static $tpl_component = "Modules/StudyProgramme";
    
    protected $indent = 0;
    
    public function setIndent($a_indent)
    {
        assert(is_int($a_indent));
        assert($a_indent > 0);
        $this->indent = $a_indent;
    }
    
    public function getIndent()
    {
        return $this->indent;
    }
    
    // This should be doing something else originally, but i need some
    // kind of hook in ilObjectListGUI::getListItemHTML and chose this,
    // as it is called at last.
    public function insertSubItems()
    {
        parent::insertSubItems();
        for ($i = 0; $i < $this->getIndent(); $i++) {
            $this->tpl->touchBlock("indent");
        }
    }
}
