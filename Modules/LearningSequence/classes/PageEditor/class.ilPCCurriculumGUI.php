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
 * Curriculum for PageEditor, the GUI
 *
 * @ilCtrl_isCalledBy ilPCCurriculumGUI: ilPageEditorGUI
 */
class ilPCCurriculumGUI extends ilPageContentGUI
{
    const CMD_INSERT = 'insert';
    const CMD_EDIT = 'edit';

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_EDIT);
                switch ($cmd) {
                    
                    case self::CMD_INSERT:
                        $this->insertNewContentObj();
                        // no break
                    case self::CMD_EDIT:
                        $this->returnToParent();
                        break;

                    default:
                        throw new Exception('unknown command: ' . $cmd);
                }
        }
    }

    protected function returnToParent() : void
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    protected function createNewPageContent() : ilPCCurriculum
    {
        return new ilPCCurriculum(
            $this->getPage()
        );
    }

    public function insertNewContentObj() : void
    {
        $this->content_obj = $this->createNewPageContent();
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->pg_obj->update();
    }
}
