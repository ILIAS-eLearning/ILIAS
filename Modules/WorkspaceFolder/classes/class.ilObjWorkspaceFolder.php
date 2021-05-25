<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjWorkspaceFolder
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjWorkspaceFolder extends ilObject2
{
    public $folder_tree;

    /**
     * @var ilObjUser
     */
    protected $current_user;

    /**
     * Constructor
     * @access	public
     * @param	integer	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);

        $this->current_user = $DIC->user();
    }

    public function initType()
    {
        $this->type = "wfld";
    }

    public function setFolderTree($a_tree)
    {
        $this->folder_tree = &$a_tree;
    }
    
    /**
     * Clone folder
     *
     * @access public
     * @param object clone
     * @param int target id
     * @param int copy id
     */
    public function doCloneObject($a_new_object, $a_target_id, $a_copy_id = 0)
    {
    }

    /**
     * Clone object dependencies (crs items, preconditions)
     *
     * @access public
     * @param int target ref id of new course
     * @param int copy id
     *
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
    }

    /**
    * Get container view mode
    */
    public function getViewMode()
    {
        return ilContainer::VIEW_BY_TYPE;
    }

    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(&$a_item_data)
    {
    }

    /**
     * @return bool
     */
    public function gotItems($node_id)
    {
        $tree = new ilWorkspaceTree($this->current_user->getId());
        $nodes = $tree->getChilds($node_id, "title");

        if (sizeof($nodes)) {
            return true;
        }
        return false;
    }
}
