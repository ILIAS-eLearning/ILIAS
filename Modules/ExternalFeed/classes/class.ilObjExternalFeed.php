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
	function ilObjExternalFeed($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "feed";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		if (!parent::update())
		{			
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
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB, $ilLog;
		
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	$fb = $this->getFeedBlock();
		
		include_once("./Services/Block/classes/class.ilExternalFeedBlock.php");
		$new_feed_block = new ilExternalFeedBlock();
		$new_feed_block->setContextObjId($new_obj->getId());
		$new_feed_block->setContextObjType("feed");

		if (is_object($fb))
		{
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
	function delete()
	{		
		// always call parent delete function first!!
		if (!parent::delete())
		{
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
		foreach($c_blocks as $c_block)		// should be usually only one
		{
			if ($c_block["type"] == "feed")
			{
				$fb = new ilExternalFeedBlock($c_block["id"]);
				$fb->delete();
				include_once("./Services/Block/classes/class.ilBlockSetting.php");
				ilBlockSetting::_deleteSettingsOfBlock($c_block["id"], "feed");
			}
		}

		//ilBlockSetting::_lookupSide($type, $user_id, $c_block["id"]);
		
		return true;
	}

	function getFeedBlock()
	{
		global $ilLog;
		
		// delete feed block
		include_once("./Services/Block/classes/class.ilCustomBlock.php");
		$costum_block = new ilCustomBlock();
		$costum_block->setContextObjId($this->getId());
		$costum_block->setContextObjType($this->getType());
		$c_blocks = $costum_block->queryBlocksForContext();
		
		include_once("./Services/Block/classes/class.ilExternalFeedBlock.php");
		foreach($c_blocks as $c_block)		// should be usually only one
		{
			if ($c_block["type"] == "feed")
			{
				$fb = new ilExternalFeedBlock($c_block["id"]);
				return $fb;
			}
		}

		return false;
	}
	

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;
		
		switch ($a_event)
		{
			case "link":
				
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "cut":
				
				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":
				
				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "new":
				
				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}

		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}
		
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}

} // END class.ilObjExternalFeed
?>
