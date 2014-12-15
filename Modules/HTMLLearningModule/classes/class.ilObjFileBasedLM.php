<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ModulesHTMLLearningModule Modules/HTMLLearningModule
 */

require_once "./Services/Object/classes/class.ilObject.php";
//require_once "Services/MetaData/classes/class.ilMDLanguageItem.php";

/**
* File Based Learning Module (HTML) object
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ingroup ModulesHTMLLearningModule
*/
class ilObjFileBasedLM extends ilObject
{
	var $tree;
	
	protected $online; // [bool]
	protected $show_license; // [bool]
	protected $show_bib; // [bool]

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
		
		$this->setShowLicense(false);
		$this->setShowBibliographicalData(false);
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
		
		$this->updateMetaData();
		parent::update();

		$ilDB->manipulate("UPDATE file_based_lm SET ".
			" is_online = ".$ilDB->quote(ilUtil::tf2yn($this->getOnline()), "text").
			", startfile = ".$ilDB->quote($this->getStartFile(), "text")." ".
			", show_lic = ".$ilDB->quote($this->getShowLicense(), "integer")." ".
			", show_bib = ".$ilDB->quote($this->getShowBibliographicalData(), "integer")." ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer"));

		return true;
	}

	/**
	* read object
	*/
	function read()
	{
		global $ilDB;
		
		parent::read();

		$q = "SELECT * FROM file_based_lm WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$lm_set = $ilDB->query($q);
		$lm_rec = $ilDB->fetchAssoc($lm_set);
		$this->setOnline(ilUtil::yn2tf($lm_rec["is_online"]));
		$this->setStartFile((string) $lm_rec["startfile"]);
		$this->setShowLicense($lm_rec["show_lic"]);
		$this->setShowBibliographicalData($lm_rec["show_bib"]);
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

		$ilDB->manipulate("INSERT INTO file_based_lm (id, is_online, startfile) VALUES ".
			" (".$ilDB->quote($this->getID(), "integer").",".
			$ilDB->quote("n", "text").",".
			$ilDB->quote($this->getStartfile(), "text").")");

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
		if($a_file &&
			file_exists($this->getDataDirectory()."/".$a_file))
		{				
			$this->start_file = $a_file;
		}
	}

	function setOnline($a_online)
	{
		$this->online = $a_online;
	}

	function getOnline()
	{
		return $this->online;
	}
	
	function setShowLicense($a_value)
	{
		$this->show_license = (bool)$a_value;
	}
	
	function getShowLicense()
	{
		return $this->show_license;
	}
	
	function setShowBibliographicalData($a_value)
	{
		$this->show_bib = (bool)$a_value;
	}
	
	function getShowBibliographicalData()
	{
		return $this->show_bib;
	}

	/**
	* check wether content object is online
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;
		
		$q = "SELECT * FROM file_based_lm WHERE id = ".$ilDB->quote($a_id, "integer");
		$lm_set = $ilDB->query($q);
		$lm_rec = $ilDB->fetchAssoc($lm_set);

		return ilUtil::yn2tf($lm_rec["is_online"]);
	}

	/**
	* Gets the disk usage of the object in bytes.
    *
	* @access	public
	* @return	integer		the disk usage in bytes
	*/
	function getDiskUsage()
	{
	    require_once("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php");
		return ilObjFileBasedLMAccess::_lookupDiskUsage($this->id);
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
		include_once("./Services/Xml/classes/class.ilNestedSetXML.php");
		$nested = new ilNestedSetXML();
		$nested->init($this->getId(), "bib");
		$nested->deleteAllDBData();

		// delete file_based_lm record
		$ilDB->manipulate("DELETE FROM file_based_lm WHERE id = ".
			$ilDB->quote($this->getID(), "integer"));

		// delete data directory
		ilUtil::delDir($this->getDataDirectory());

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
	 * Populate by directory. Add a filename to do a special check for
	 * ILIAS HTML export files. If the corresponding directory is found
	 * within the passed directory path (i.e. "htlm_<id>") this
	 * subdirectory is used instead.
	 *
	 * @param
	 * @return
	 */
	function populateByDirectoy($a_dir, $a_filename = "")
	{
		preg_match("/.*htlm_([0-9]*)\.zip/", $a_filename, $match);
		if (is_dir($a_dir."/htlm_".$match[1]))
		{
			$a_dir = $a_dir."/htlm_".$match[1];
		}
		ilUtil::rCopy($a_dir, $this->getDataDirectory());
		ilUtil::renameExecutables($this->getDataDirectory());
	}
	
	/**
	 * Clone HTML learning module
	 *
	 * @param int target ref_id
	 * @param int copy id
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB, $ilUser, $ilias;

		$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	$this->cloneMetaData($new_obj);
	 	
		$new_obj->setTitle($this->getTitle());
		$new_obj->setDescription($this->getDescription());
		$new_obj->setShowLicense($this->getShowLicense());
		$new_obj->setShowBibliographicalData($this->getShowBibliographicalData());

		// copy content
		$new_obj->populateByDirectoy($this->getDataDirectory());

		$new_obj->setStartFile($this->getStartFile());
		$new_obj->update();

		return $new_obj;
	}

}
?>
