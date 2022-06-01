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
 * Class ilStudyProgrammeCourseListGUI
 *
 * ilObjCourseListGUI with possibility for identations, used for study programme view on personal desktop.
 */
class ilStudyProgrammeCourseListGUI extends ilObjCourseListGUI
{
    protected static string $tpl_file_name = "tpl.course_list_item.html";
    protected static string $tpl_component = "Modules/StudyProgramme";
    
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
