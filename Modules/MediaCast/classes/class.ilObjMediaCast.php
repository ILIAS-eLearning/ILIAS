<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjMediaCast
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
*/
class ilObjMediaCast extends ilObject
{
	public static $purposes = array ("Standard", "VideoAlternative", "VideoPortable", "AudioPortable");    
    protected $online = false;
	protected $publicfiles = false;
	protected $downloadable = true;
	protected $order;
	protected $view_mode = "";
	
	const ORDER_TITLE = 1;
	const ORDER_CREATION_DATE_ASC = 2;
	const ORDER_CREATION_DATE_DESC = 3;
	const ORDER_MANUAL = 4;
	
	const VIEW_LIST = "";
	const VIEW_GALLERY = "gallery";

	/**
	 * access to rss news
	 *
	 * @var 0 = logged in users, 1 = public access
	 */
	protected $defaultAccess = 0;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjMediaCast($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "mcst";
		$this->ilObject($a_id,$a_call_by_reference);
		$mcst_set = new ilSetting("mcst");	
		$this->setDefaultAccess($mcst_set->get("defaultaccess") == "users" ? 0 : 1);
		$this->setOrder(self::ORDER_CREATION_DATE_DESC);
	}

	/**
	* Set Online.
	*
	* @param	boolean	$a_online	Online
	*/
	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	/**
	* Get Online.
	*
	* @return	boolean	Online
	*/
	function getOnline()
	{
		return $this->online;
	}

	/**
	* Set PublicFiles.
	*
	* @param	boolean	$a_publicfiles	PublicFiles
	*/
	function setPublicFiles($a_publicfiles)
	{
		$this->publicfiles = $a_publicfiles;
	}

	/**
	* Get PublicFiles.
	*
	* @return	boolean	PublicFiles
	*/
	function getPublicFiles()
	{
		return $this->publicfiles;
	}

	/**
	 * Set view mode
	 *
	 * @param string $a_val view mode	
	 */
	function setViewMode($a_val)
	{
		$this->view_mode = $a_val;
	}
	
	/**
	 * Get view mode
	 *
	 * @return string view mode
	 */
	function getViewMode()
	{
		return $this->view_mode;
	}
	/**
	* Set ItemsArray.
	*
	* @param	array	$a_itemsarray	ItemsArray
	*/
	function setItemsArray($a_itemsarray)
	{
		$this->itemsarray = $a_itemsarray;
	}

	/**
	* Get ItemsArray.
	*
	* @return	array	ItemsArray
	*/
	function getItemsArray()
	{
		return $this->itemsarray;
	}

	/**
	 * Get sorted items array
	 *
	 * @param
	 * @return
	 */
	function getSortedItemsArray()
	{
		$med_items = $this->getItemsArray();

		// sort by order setting
		switch($this->getOrder())
		{
			case ilObjMediaCast::ORDER_TITLE:
				$med_items = ilUtil::sortArray($med_items, "title", "asc", false, true);
				break;
			
			case ilObjMediaCast::ORDER_CREATION_DATE_ASC:
				$med_items = ilUtil::sortArray($med_items, "creation_date", "asc", false, true);
				break;
			
			case ilObjMediaCast::ORDER_CREATION_DATE_DESC:
				$med_items = ilUtil::sortArray($med_items, "creation_date", "desc", false, true);
				break;
			
			case ilObjMediaCast::ORDER_MANUAL:
				$order = array_flip($this->readOrder());		
				$pos = sizeof($order);
				foreach(array_keys($med_items) as $idx)
				{
					if(array_key_exists($idx, $order))
					{
						$med_items[$idx]["order"] = ($order[$idx]+1)*10;
					}
					// item has no order yet
					else
					{
						$med_items[$idx]["order"] = (++$pos)*10;
					}
				}	
				
				$med_items = ilUtil::sortArray($med_items, "order", "asc", true, true);
				break;			
		}

		return $med_items;
	}
	
	
	/**
	* Set Downloadable.
	*
	* @param	boolean	$a_downloadable	Downloadable
	*/
	function setDownloadable($a_downloadable)
	{
		$this->downloadable = $a_downloadable;
	}
	/**
	* Get Downloadable.
	*
	* @return	boolean	Downloadable
	*/
	function getDownloadable()
	{
		return $this->downloadable;
	}
	
	/**
	 * return default access for news items
	 *
	 * @return int 0 for logged in users, 1 for public access
	 */
	function getDefaultAccess() {
	    return $this->defaultAccess;
	}
	
	/**
	 * set default access: 0 logged in users, 1 for public access
	 *
	 * @param int $value
	 */
	function setDefaultAccess($value) {
	    $this->defaultAccess = (int) $value == 0 ? 0 : 1;
	}
	
	/**
	* Set order.
	*
	* @param	boolean	$a_value	
	*/
	function setOrder($a_value)
	{
		$this->order = $a_value;
	}
	/**
	* Get order.
	*
	* @return	boolean	
	*/
	function getOrder()
	{
		return $this->order;
	}
	
	/**
	* Gets the disk usage of the object in bytes.
    *
	* @access	public
	* @return	integer		the disk usage in bytes
	*/
	function getDiskUsage()
	{
	    require_once("./Modules/MediaCast/classes/class.ilObjMediaCastAccess.php");
		return ilObjMediaCastAccess::_lookupDiskUsage($this->id);
	}
	
	/**
	* Create mew media cast
	*/
	function create()
	{
		global $ilDB;

		parent::create();
		
		$query = "INSERT INTO il_media_cast_data (".
			" id".
			", is_online".
			", public_files".
			", downloadable".
		    ", def_access".
			", sortmode".
			", viewmode".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote((int) $this->getOnline(), "integer")
			.",".$ilDB->quote((int) $this->getPublicFiles(), "integer")
			.",".$ilDB->quote((int) $this->getDownloadable(), "integer")
			.",".$ilDB->quote((int) $this->getDefaultAccess(), "integer")
			.",".$ilDB->quote((int) $this->getOrder(), "integer")
			.",".$ilDB->quote((int) $this->getViewMode(), "text")			
			.")";
		$ilDB->manipulate($query);

	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		global $ilDB;
		
		if (!parent::update())
		{			
			return false;
		}

		// update media cast data
		$query = "UPDATE il_media_cast_data SET ".
			" is_online = ".$ilDB->quote((int) $this->getOnline(), "integer").
			", public_files = ".$ilDB->quote((int) $this->getPublicFiles(), "integer").
			", downloadable = ".$ilDB->quote((int) $this->getDownloadable(), "integer").
		    ", def_access = ".$ilDB->quote((int) $this->getDefaultAccess(), "integer").
		    ", sortmode = ".$ilDB->quote((int) $this->getOrder(), "integer").
		    ", viewmode = ".$ilDB->quote($this->getViewMode(), "text").
			" WHERE id = ".$ilDB->quote((int) $this->getId(), "integer");

		$ilDB->manipulate($query);

		return true;
	}
	
	/**
	* Read media cast
	*/
	function read()
	{
		global $ilDB;
		
		parent::read();
		$this->readItems();
		
		$query = "SELECT * FROM il_media_cast_data WHERE id = ".
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setOnline($rec["is_online"]);
		$this->setPublicFiles($rec["public_files"]);
		$this->setDownloadable($rec["downloadable"]);
		$this->setDefaultAccess($rec["def_access"]);
		$this->setOrder($rec["sortmode"]);
		$this->setViewMode($rec["viewmode"]);
		
	}


	/**
	* delete object and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete all items
		$med_items = $this->getItemsArray();
		foreach ($med_items as $item)
		{
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$news_item = new ilNewsItem($item["id"]);
			$news_item->delete();
		}
		
		$this->deleteOrder();

		// delete record of table il_media_cast_data
		$query = "DELETE FROM il_media_cast_data".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		
		return true;
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

	/**
	* Get all items of media cast.
	*/
	function readItems($a_oldest_first = false)
	{		
		//
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$it = new ilNewsItem();
		$it->setContextObjId($this->getId());
		$it->setContextObjType($this->getType());
		$this->itemsarray = $it->queryNewsForContext(false, 0, "", false, $a_oldest_first);
		
		return $this->itemsarray;
	}

	function deleteOrder()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$sql = "DELETE FROM il_media_cast_data_ord".
			" WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($sql);
	}
	
	function readOrder()
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$all = array();		
		$sql = "SELECT item_id FROM il_media_cast_data_ord".
			" WHERE obj_id = ".$ilDB->quote($this->getId(), "integer").
			" ORDER BY pos";	
		$res = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($res))
		{
			$all[] = $row["item_id"];
		}
		return $all;
	}
	
	function saveOrder(array $a_items)
	{
		global $ilDB;
		
		if(!$this->getId())
		{
			return;
		}
		
		$this->deleteOrder();
		
		$pos = 0;
		foreach($a_items as $item_id)
		{
			$pos++;
			
			$sql = "INSERT INTO il_media_cast_data_ord (obj_id,item_id,pos)".
				" VALUES (".$ilDB->quote($this->getId(), "integer").",".
				$ilDB->quote($item_id, "integer").",".
				$ilDB->quote($pos, "integer").")";
			$ilDB->manipulate($sql);
		}
	}
	
	/**
	 * Clone media cast
	 *
	 * @param int target ref_id
	 * @param int copy id
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB, $ilUser, $ilias;

		$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	
		$new_obj->setTitle($this->getTitle());
		$new_obj->setPublicFiles($this->getPublicFiles());
		$new_obj->setDownloadable($this->getDownloadable());
		$new_obj->setDefaultAccess($this->getDefaultAccess());
		$new_obj->setOrder($this->getOrder());
		$new_obj->update();

		// copy items
		$this->copyItems($new_obj);
		
		// copy order!?

		return $new_obj;
	}

	/**
	 * Copy items
	 *
	 * @param
	 * @return
	 */
	function copyItems($a_new_obj)
	{
		global $ilUser;
		
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		foreach($this->readItems(true) as $item)
		{
			// copy media object
			$mob_id = $item["mob_id"];
			$mob = new ilObjMediaObject($mob_id);
			$new_mob = $mob->duplicate();
			
			// copy news item
			// create new media cast item
			include_once("./Services/News/classes/class.ilNewsItem.php");
			$mc_item = new ilNewsItem();
			$mc_item->setMobId($new_mob->getId());
			$mc_item->setContentType(NEWS_AUDIO);
			$mc_item->setContextObjId($a_new_obj->getId());
			$mc_item->setContextObjType($a_new_obj->getType());
			$mc_item->setUserId($ilUser->getId());
			$mc_item->setPlaytime($item["playtime"]);
			$mc_item->setTitle($item["title"]);
			$mc_item->setContent($item["content"]);
			$mc_item->setVisibility($item["visibility"]);
			$mc_item->create();

		}
	}
	
}
?>
