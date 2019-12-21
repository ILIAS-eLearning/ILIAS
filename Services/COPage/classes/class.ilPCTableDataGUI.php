<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCTableData.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCTableDataGUI
*
* Handles user commands on table data elements (table cells)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCTableDataGUI extends ilPageContentGUI
{

    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
    * insert new row after cell
    */
    public function newRowAfter()
    {
        $this->content_obj->newRowAfter();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * insert new row before cell
    */
    public function newRowBefore()
    {
        $this->content_obj->newRowBefore();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * delete a row
    */
    public function deleteRow()
    {
        $this->content_obj->deleteRow();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }


    /**
    * insert new col after cell
    */
    public function newColAfter()
    {
        $this->content_obj->newColAfter();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * insert new col before cell
    */
    public function newColBefore()
    {
        $this->content_obj->newColBefore();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * delete column
    */
    public function deleteCol()
    {
        $this->content_obj->deleteCol();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move row down
    */
    public function moveRowDown()
    {
        $this->content_obj->moveRowDown();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move list item up
    */
    public function moveRowUp()
    {
        $this->content_obj->moveRowUp();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move column right
    */
    public function moveColRight()
    {
        $this->content_obj->moveColRight();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move list item up
    */
    public function moveColLeft()
    {
        $this->content_obj->moveColLeft();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
