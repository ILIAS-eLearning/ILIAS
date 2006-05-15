<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* Class ilObjAssessmentFolder
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjAssessmentFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjAssessmentFolder($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "assf";
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
	* copy all entries of your object.
	*
	* @access	public
	* @param	integer	ref_id of parent object
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{
		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);

		// get object instance of ilCloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");

		// always destroy objects in ilClone method because ilClone() is recursive and creates instances for each object in subtree!
		//unset($newObj);

		// ... and finally always return new reference ID!!
		return $new_ref_id;
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
	* enable assessment logging
	*/
	function _enableAssessmentLogging($a_enable)
	{
		global $ilias;

		if ($a_enable)
		{
			$ilias->setSetting("assessment_logging", 1);
		}
		else
		{
			$ilias->setSetting("assessment_logging", 0);
		}
	}

	/**
	* set the log language
	*/
	function _setLogLanguage($a_language)
	{
		global $ilias;

		$ilias->setSetting("assessment_log_language", $a_language);
	}

	/**
	* check wether assessment logging is enabled or not
	*/
	function _enabledAssessmentLogging()
	{
		global $ilias;

		return (boolean) $ilias->getSetting("assessment_logging");
	}
	
	/**
	* retrieve the log language for assessment logging
	*/
	function _getLogLanguage()
	{
		global $ilias;

		$lang = $ilias->getSetting("assessment_log_language");
		if (strlen($lang) == 0)
		{
			$lang = "en";
		}
		return $lang;
	}

	/**
	* Add an assessment log entry
	*
	* Add an assessment log entry
	*
	* @param integer $user_id The user id of the acting user
	* @param integer $object_id The database id of the modified test object
	* @param string $logtext The textual description for the log entry
	* @param integer $question_id The database id of a modified question (optional)
	* @param integer $original_id The database id of the original of a modified question (optional)
	* @return array Array containing the datasets between $ts_from and $ts_to for the test with the id $test_id
	*/
	function _addLog($user_id, $object_id, $logtext, $question_id = "", $original_id = "")
	{
		global $ilUser, $ilDB;
		if (strlen($question_id) == 0)
		{
			$question_id = "NULL";
		}
		else
		{
			$question_id = $ilDB->quote($question_id . "");
		}
		if (strlen($original_id) == 0)
		{
			$original_id = "NULL";
		}
		else
		{
			$original_id = $ilDB->quote($original_id . "");
		}
		$query = sprintf("INSERT INTO ass_log (ass_log_id, user_fi, obj_fi, logtext, question_fi, original_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($object_id . ""),
			$ilDB->quote($logtext . ""),
			$question_id,
			$original_id
		);
		$result = $ilDB->query($query);
	}
	
	/**
	* Retrieve assessment log datasets from the database
	*
	* Retrieve assessment log datasets from the database
	*
	* @param string $ts_from Timestamp of the starting date/time period
	* @param string $ts_to Timestamp of the ending date/time period
	* @param integer $test_id Database id of the ILIAS test object
	* @return array Array containing the datasets between $ts_from and $ts_to for the test with the id $test_id
	*/
	function &getLog($ts_from, $ts_to, $test_id)
	{
		$log = array();
		$query = sprintf("SELECT *, TIMESTAMP + 0 AS TIMESTAMP14 FROM ass_log WHERE obj_fi = %s AND TIMESTAMP + 0 > %s AND TIMESTAMP + 0 < %s ORDER BY TIMESTAMP14",
			$this->ilias->db->quote($test_id . ""),
			$this->ilias->db->quote($ts_from . ""),
			$this->ilias->db->quote($ts_to . "")
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (!array_key_exists($row["TIMESTAMP14"], $log))
			{
				$log[$row["TIMESTAMP14"]] = array();
			}
			array_push($log[$row["TIMESTAMP14"]], $row);
		}
		ksort($log);
		// flatten array
		$log_array = array();
		foreach ($log as $key => $value)
		{
			foreach ($value as $index => $row)
			{
				array_push($log_array, $row);
			}
		}
		return $log_array;
	}
	
	/**
	* Returns the number of log entries for a given test id
	*
	* Returns the number of log entries for a given test id
	*
	* @param integer $test_obj_id Database id of the ILIAS test object
	* @return integer The number of log entries for the test object
	*/
	function getNrOfLogEntries($test_obj_id)
	{
		$query = sprintf("SELECT COUNT(obj_fi) AS logcount FROM ass_log WHERE obj_fi = %s",
			$this->ilias->db->quote($test_obj_id . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["logcount"];
		}
		else
		{
			return 0;
		}
	}
	
	/**
	* Returns an array of all allowed HTML tags for text editing
	*
	* Returns an array of all allowed HTML tags for text editing
	*
	* @return array HTML tags
	*/
	function &_getUsedHTMLTags()
	{
		global $ilias;

		$usedtags = array();
		$tags = $ilias->getSetting("assessment_settings_used_html_tags");
		if (strlen($tags))
		{
			$usedtags = unserialize($tags);
		}
		else
		{
			$usedtags = array(
				"cite",
				"code",
				"em",
				"strong"
			);
		}
		return $usedtags;
	}
	
	function _getUsedHTMLTagsAsString()
	{
		$tags =& ilObjAssessmentFolder::_getUsedHTMLTags();
		$result = "";
		foreach ($tags as $tag)
		{
			$result .= "<" . $tag . ">";
		}
		return $result;
	}
	
	/**
	* Writes an array with allowed HTML tags to the ILIAS settings
	*
	* Writes an array with allowed HTML tags to the ILIAS settings
	*
	* @param array $a_html_tags An array containing the allowed HTML tags
	*/
	function _setUsedHTMLTags($a_html_tags)
	{
		global $ilias;

		$ilias->setSetting("assessment_settings_used_html_tags", serialize($a_html_tags));
	}
	
	/**
	* Returns an array of all possible HTML tags for text editing
	*
	* Returns an array of all possible HTML tags for text editing
	*
	* @return array HTML tags
	*/
	function &getHTMLTags()
	{
		$tags = array(
			"a",
			"big",
			"blockquote",
			"br",
			"center",
			"cite",
			"code",
			"del",
			"em",
			"h1",
			"h2",
			"h3",
			"h4",
			"h5",
			"h6",
			"hr",
			"img",
			"ins",
			"li",
			"ol",
			"p",
			"pre",
			"small",
			"strike",
			"strong",
			"sub",
			"sup",
			"u",
			"ul"			
		);
		return $tags;
	}
} // END class.ilObjAssessmentFolder
?>
