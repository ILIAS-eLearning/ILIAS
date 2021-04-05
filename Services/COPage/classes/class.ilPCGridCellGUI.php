<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Grid cell UI class
 *
 * @author Alex Killing <killing@leifos.de>
 */
class ilPCGridCellGUI extends ilPageContentGUI
{

    /**
     * Constructor
     */
    public function __construct($a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * delete cell
     */
    public function deleteCell()
    {
        $this->content_obj->deleteCell();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }


    /**
     * Move cell right
     */
    public function moveCellRight()
    {
        $this->content_obj->moveCellRight();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * Move cell left
     */
    public function moveCellLeft()
    {
        $this->content_obj->moveCellLeft();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
