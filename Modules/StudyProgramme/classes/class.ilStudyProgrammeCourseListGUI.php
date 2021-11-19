<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Class ilStudyProgrammeCourseListGUI
 *
 * ilObjCourseListGUI with possibility for identations, used for study programme view on personal desktop.
 */
class ilStudyProgrammeCourseListGUI extends ilObjCourseListGUI
{
    /**
     * @var string
     */
    protected static $tpl_file_name = "tpl.course_list_item.html";

    /**
     * @var string
     */
    protected static $tpl_component = "Modules/StudyProgramme";
    
    protected int $indent = 0;
    
    public function setIndent(int $a_indent) : void
    {
        assert($a_indent > 0);
        $this->indent = $a_indent;
    }
    
    public function getIndent() : int
    {
        return $this->indent;
    }
    
    // This should be doing something else originally, but i need some
    // kind of hook in ilObjectListGUI::getListItemHTML and chose this,
    // as it is called at last.
    public function insertSubItems() : void
    {
        parent::insertSubItems();
        for ($i = 0; $i < $this->getIndent(); $i++) {
            $this->tpl->touchBlock("indent");
        }
    }
}
