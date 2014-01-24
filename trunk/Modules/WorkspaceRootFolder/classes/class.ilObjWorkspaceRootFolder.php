<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
* Class ilObjWorkspaceRootFolder
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilObjRootFolder.php 23143 2010-03-09 12:15:33Z smeyer $Id: class.ilObjRootFolder.php,v 1.12 2003/11/20 17:04:19 shofmann Exp $
*
* @extends ilObject2
*/
class ilObjWorkspaceRootFolder extends ilObject2
{
	function initType()
	{
		$this->type = "wsrt";
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
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
				//echo "RootFolder ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "cut":

				//echo "RootFolder ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "copy":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "RootFolder ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":

				//echo "RootFolder ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "new":

				//echo "RootFolder ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}


		return true;
	}

	/**
	* get all translations from this category
	*
	* @access	public
	* @return	array
	*/
	function getTranslations()
	{
		global $ilDB;

		$q = "SELECT * FROM object_translation WHERE obj_id = ".
			$ilDB->quote($this->getId(),'integer')." ORDER BY lang_default DESC";
		$r = $this->ilias->db->query($q);

		$num = 0;

		$data["Fobject"] = array();
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data["Fobject"][$num]= array("title"	=> $row->title,
										  "desc"	=> $row->description,
										  "lang"	=> $row->lang_code
										  );
			$num++;
		}

		// first entry is always the default language
		$data["default_language"] = 0;

		return $data ? $data : array();
	}

	// remove all Translations of current category
	function removeTranslations()
	{
		global $ilDB;

		$query = "DELETE FROM object_translation WHERE obj_id= ".
			$ilDB->quote($this->getId(),'integer');
		$res = $ilDB->manipulate($query);
	}

	// add a new translation to current category
	function addTranslation($a_title,$a_desc,$a_lang,$a_lang_default)
	{
		global $ilDB;

		if (empty($a_title))
		{
			$a_title = "NO TITLE";
		}

		$query = "INSERT INTO object_translation ".
			 "(obj_id,title,description,lang_code,lang_default) ".
			 "VALUES ".
			 "(".$ilDB->quote($this->getId(),'integer').",".
			 $ilDB->quote($a_title,'text').",".
			 $ilDB->quote($a_desc,'text').",".
			 $ilDB->quote($a_lang,'text').",".
			 $ilDB->quote($a_lang_default,'integer').")";
		$res = $ilDB->manipulate($query);
		return true;
	}

} // END class.ObjRootFolder
?>
