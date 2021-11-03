<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilPCTableDataGUI
 * Handles user commands on table data elements (table cells)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTableDataGUI extends ilPageContentGUI
{
    public function __construct(
        ilPageObject $a_pg_obj,
        ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand() : void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }


    public function newRowAfter() : void
    {
        $this->content_obj->newRowAfter();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function newRowBefore() : void
    {
        $this->content_obj->newRowBefore();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function deleteRow() : void
    {
        $this->content_obj->deleteRow();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function newColAfter() : void
    {
        $this->content_obj->newColAfter();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function newColBefore() : void
    {
        $this->content_obj->newColBefore();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function deleteCol() : void
    {
        $this->content_obj->deleteCol();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function moveRowDown() : void
    {
        $this->content_obj->moveRowDown();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function moveRowUp() : void
    {
        $this->content_obj->moveRowUp();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function moveColRight() : void
    {
        $this->content_obj->moveColRight();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    public function moveColLeft() : void
    {
        $this->content_obj->moveColLeft();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
