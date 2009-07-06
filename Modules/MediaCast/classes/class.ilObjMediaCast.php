<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once "./classes/class.ilObject.php";

/**
* Class ilObjMediaCast
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
*/
class ilObjMediaCast extends ilObject
{
	public static $purposes = array ("Standard","VideoPortable", "AudioPortable");    
    protected $online = false;
	protected $publicfiles = false;
	protected $downloadable = true;
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
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote((int) $this->getOnline(), "integer")
			.",".$ilDB->quote((int) $this->getPublicFiles(), "integer")
			.",".$ilDB->quote((int) $this->getDownloadable(), "integer")
			.",".$ilDB->quote((int) $this->getDefaultAccess(), "integer")
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
		
		// delete record of table il_media_cast_data
		$query = "DELETE FROM il_media_cast_data".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		
		return true;
	}

	/**
	* init default roles settings
	* 
	* If your module does not require any default roles, delete this method 
	* (For an example how this method is used, look at ilObjForum)
	* 
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;
		
		// create a local role folder
		//$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of forum obj_no.".$this->getId());

		// create moderator role and assign role to rolefolder...
		//$roleObj = $rfoldObj->createRole("Moderator","Moderator of forum obj_no.".$this->getId());
		//$roles[] = $roleObj->getId();

		//unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
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
	function readItems()
	{
		global $ilDB;
		
		//
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$it = new ilNewsItem();
		$it->setContextObjId($this->getId());
		$it->setContextObjType($this->getType());
		$this->itemsarray = $it->queryNewsForContext(false);
		
		return $this->itemsarray;
	}
	
	
} // END class.ilObjMediaCast
?>
