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
 * Handles the user interface for learning map page content.
 *
 * @ilCtrl_isCalledBy ilPCLearningMapGUI: ilPageEditorGUI
 */
class ilPCLearningMapGUI extends ilPageContentGUI
{
    public const CMD_INSERT = 'insert';
    public const CMD_EDIT = 'edit';

    /**
     * Executes the requested command.
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd(self::CMD_EDIT);
                switch ($cmd) {
                    case self::CMD_INSERT:
                        $this->insertNewContentObj();
                        $this->returnToParent();
                        break;
                    case self::CMD_EDIT:
                        $this->returnToParent();
                        break;

                    default:
                        throw new Exception('unknown command: ' . $cmd);
                }
        }
    }

    /**
     * Returns to the parent controller.
     */
    protected function returnToParent(): void
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * Creates a learning map page content object.
     */
    protected function createNewPageContent(): ilPCLearningMap
    {
        return new ilPCLearningMap(
            $this->getPage()
        );
    }

    /**
     * Inserts a new learning map page content object.
     */
    public function insertNewContentObj(): void
    {
        $this->content_obj = $this->createNewPageContent();
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->pg_obj->update();
    }
}
