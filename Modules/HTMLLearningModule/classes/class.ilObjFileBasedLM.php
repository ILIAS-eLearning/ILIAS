<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

/** @defgroup ModulesHTMLLearningModule Modules/HTMLLearningModule
 */

/**
* File Based Learning Module (HTML) object
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ingroup ModulesHTMLLearningModule
*/

require_once "classes/class.ilObject.php";
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";

class ilObjFileBasedLM extends ilObject
{
	var $tree;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjFileBasedLM($a_id = 0,$a_call_by_reference = true)
	{
		// this also calls read() method! (if $a_id is set)
		$this->type = "htlm";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* get title of content object
	*
	* @return	string		title
	*/
/*
	function getTitle()
	{
		return parent::getTitle();
		//return $this->meta_data->getTitle();
	}
*/

	/**
	* set title of content object
	*
	* @param	string	$a_title		title
	*/
/*
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
		$this->meta_data->setTitle($a_title);
	}
*/

	/**
	* get description of content object
	*
	* @return	string		description
	*/
/*
	function getDescription()
	{
		return $this->meta_data->getDescription();
	}
*/

	/**
	* set description of content object
	*
	* @param	string	$a_description		description
	*/
/*
	function setDescription($a_description)
	{
		$this->meta_data->setDescription($a_description);
	}
*/

	/**
	* assign a meta data object to content object
	*
	* @param	object		$a_meta_data	meta data object
	*/
/*
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}
*/

	/**
	* get meta data object of content object
	*
	* @return	object		meta data object
	*/
/*
	function &getMetaData()
	{
		return $this->meta_data;
	}
*/

	/**
	* update meta data only
	*/
/*
	function updateMetaData()
	{
		$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->meta_data->setTitle($meta[0]["value"]);
			$meta = $this->meta_data->getElement("Description", "General");
			$this->meta_data->setDescription($meta[0]["value"]);
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		parent::update();
	}
*/

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		global $ilDB;
		
		$this->updateMetaData();
		parent::update();

		$q = "UPDATE file_based_lm SET ".
			" online = ".$ilDB->quote(ilUtil::tf2yn($this->getOnline())).",".
			" startfile = ".$ilDB->quote($this->getStartFile())." ".
			" WHERE id = ".$ilDB->quote($this->getId())." ";
		$this->ilias->db->query($q);

		return true;
	}

	/**
	* read object
	*/
	function read()
	{
		global $ilDB;
		
		parent::read();

		$q = "SELECT * FROM file_based_lm WHERE id = ".$ilDB->quote($this->getId());
		$lm_set = $this->ilias->db->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);
		$this->setOnline(ilUtil::yn2tf($lm_rec["online"]));
		$this->setStartFile($lm_rec["startfile"]);

	}

	/**
	*	init bib object (contains all bib item data)
	*/
	function initBibItemObject()
	{
		include_once("./Modules/LearningModule/classes/class.ilBibItem.php");

		$this->bib_obj =& new ilBibItem($this);
		$this->bib_obj->read();

		return true;
	}


	/**
	* create file based lm
	*/
	function create()
	{
		global $ilDB;

		parent::create();
		$this->createDataDirectory();

/*
		$this->meta_data->setId($this->getId());
		$this->meta_data->setType($this->getType());
		$this->meta_data->setTitle($this->getTitle());
		$this->meta_data->setDescription($this->getDescription());
		$this->meta_data->setObject($this);
		$this->meta_data->create();
*/

		$q = "INSERT INTO file_based_lm (id, online, startfile) VALUES ".
			" (".$ilDB->quote($this->getID()).",".$ilDB->quote("n").",".
			$ilDB->quote("").")";
		$ilDB->query($q);

		$this->createMetaData();
	}

	function getDataDirectory($mode = "filesystem")
	{
		$lm_data_dir = ilUtil::getWebspaceDir($mode)."/lm_data";
		$lm_dir = $lm_data_dir."/lm_".$this->getId();

		return $lm_dir;
	}

	function createDataDirectory()
	{
		ilUtil::makeDir($this->getDataDirectory());
	}

	function getStartFile()
	{
		return $this->start_file;
	}

	function setStartFile($a_file)
	{
		$this->start_file = $a_file;
	}

	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	function getOnline()
	{
		return $this->online;
	}

	/**
	* check wether content object is online
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM file_based_lm WHERE id = ".$ilDB->quote($a_id);
		$lm_set = $this->ilias->db->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);

		return ilUtil::yn2tf($lm_rec["online"]);
	}



	/**
	* delete object and all related data
	*
	* this method has been tested on may 9th 2004
	* data directory, meta data, file based lm data and bib items
	* have been deleted correctly as desired
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

		// delete meta data of content object
/*
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), $this->getType());
		$nested->deleteAllDBData();
*/

		// Delete meta data
		$this->deleteMetaData();

		// delete bibliographical items of object
		include_once("classes/class.ilNestedSetXML.php");
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), "bib");
		$nested->deleteAllDBData();

		// delete file_based_lm record
		$q = "DELETE FROM file_based_lm WHERE id = ".
			$ilDB->quote($this->getID());
		$ilDB->query($q);

		// delete data directory
		ilUtil::delDir($this->getDataDirectory());

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

}
?>
