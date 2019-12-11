<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjExternalFeed
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilObjExternalFeed extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = $DIC["ilLog"];
        $this->type = "feed";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
    {
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff

        return true;
    }

    /**
     * Clone
     *
     * @access public
     * @param int target id
     * @param int copy id
     *
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $ilDB = $this->db;
        $ilLog = $this->log;
        
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $fb = $this->getFeedBlock();
        
        include_once("./Services/Block/classes/class.ilExternalFeedBlock.php");
        $new_feed_block = new ilExternalFeedBlock();
        $new_feed_block->setContextObjId($new_obj->getId());
        $new_feed_block->setContextObjType("feed");

        if (is_object($fb)) {
            $new_feed_block->setFeedUrl($fb->getFeedUrl());
            $new_feed_block->setTitle($fb->getTitle());
        }
        $new_feed_block->create();

        return $new_obj;
    }

    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        
        //put here your module specific stuff
        
        // delete feed block
        include_once("./Services/Block/classes/class.ilCustomBlock.php");
        $costum_block = new ilCustomBlock();
        $costum_block->setContextObjId($this->getId());
        $costum_block->setContextObjType($this->getType());
        $c_blocks = $costum_block->queryBlocksForContext();
        
        include_once("./Services/Block/classes/class.ilExternalFeedBlock.php");
        foreach ($c_blocks as $c_block) {		// should be usually only one
            if ($c_block["type"] == "feed") {
                $fb = new ilExternalFeedBlock($c_block["id"]);
                $fb->delete();
                include_once("./Services/Block/classes/class.ilBlockSetting.php");
                ilBlockSetting::_deleteSettingsOfBlock($c_block["id"], "feed");
            }
        }

        //ilBlockSetting::_lookupSide($type, $user_id, $c_block["id"]);
        
        return true;
    }

    public function getFeedBlock()
    {
        $ilLog = $this->log;
        
        // delete feed block
        include_once("./Services/Block/classes/class.ilCustomBlock.php");
        $costum_block = new ilCustomBlock();
        $costum_block->setContextObjId($this->getId());
        $costum_block->setContextObjType($this->getType());
        $c_blocks = $costum_block->queryBlocksForContext();
        
        include_once("./Services/Block/classes/class.ilExternalFeedBlock.php");
        foreach ($c_blocks as $c_block) {		// should be usually only one
            if ($c_block["type"] == "feed") {
                $fb = new ilExternalFeedBlock($c_block["id"]);
                return $fb;
            }
        }

        return false;
    }
} // END class.ilObjExternalFeed
