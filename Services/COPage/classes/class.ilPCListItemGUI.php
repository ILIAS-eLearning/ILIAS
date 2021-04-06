<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilPCListItemGUI
 *
 * Handles user commands on list items
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilPCListItemGUI extends ilPageContentGUI
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
    * insert new list item after current one
    */
    public function newItemAfter()
    {
        $this->content_obj->newItemAfter();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * insert new list item before current one
    */
    public function newItemBefore()
    {
        $this->content_obj->newItemBefore();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * delete a list item
    */
    public function deleteItem()
    {
        $this->content_obj->deleteItem();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move list item down
    */
    public function moveItemDown()
    {
        $this->content_obj->moveItemDown();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
    * move list item up
    */
    public function moveItemUp()
    {
        $this->content_obj->moveItemUp();
        $_SESSION["il_pg_error"] = $this->pg_obj->update();
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
