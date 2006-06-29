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
* Class ilObjAdvancedEditing
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjAdvancedEditing extends ilObject
{
	var $setting;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjAdvancedEditing($a_id = 0,$a_call_by_reference = true)
	{
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$this->setting = new ilSetting("advanced_editing");
		$this->type = "adve";
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
	* Returns an array of all allowed HTML tags for text editing
	*
	* Returns an array of all allowed HTML tags for text editing
	*
	* @return array HTML tags
	*/
	function &_getUsedHTMLTags()
	{
		$usedtags = array();
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$setting = new ilSetting("advanced_editing");
		$tags = $setting->get("advanced_editing_used_html_tags");
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

	/**
	* Returns a string of all allowed HTML tags for text editing
	*
	* Returns a string of all allowed HTML tags for text editing
	*
	* @return string Used HTML tags
	*/
	function &_getUsedHTMLTagsAsString()
	{
		$result = "";
		$tags =& ilObjAdvancedEditing::_getUsedHTMLTags();
		foreach ($tags as $tag)
		{
			$result .= "<$tag>";
		}
		return $result;
	}
	
	/**
	* Returns the identifier for the Rich Text Editor
	*
	* Returns the identifier for the Rich Text Editor
	*
	* @return string Identifier for the Rich Text Editor
	*/
	function _getRichTextEditor()
	{
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$setting = new ilSetting("advanced_editing");
		$js = $setting->get("advanced_editing_javascript_editor");
		return $js;
	}
	
	/**
	* Sets wheather a Rich Text Editor should be used or not
	*
	* Sets wheather a Rich Text Editor should be used or not
	*
	* @param boolean $a_js_editor A boolean indicating if the JS editor should be used or not
	*/
	function _setRichTextEditor($a_js_editor)
	{
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$setting = new ilSetting("advanced_editing");
		$setting->set("advanced_editing_javascript_editor", $a_js_editor);
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
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$setting = new ilSetting("advanced_editing");
		$setting->set("advanced_editing_used_html_tags", serialize($a_html_tags));
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
			"blockquote",
			"br",
			"cite",
			"code",
			"div",
			"em",
			"h1",
			"h2",
			"h3",
			"h4",
			"h5",
			"h6",
			"hr",
			"img",
			"li",
			"ol",
			"p",
			"pre",
			"span",
			"strike",
			"strong",
			"sub",
			"sup",
			"table",
			"td",
			"tr",
			"u",
			"ul"			
		);
		return $tags;
	}
	
} // END class.ilObjAdvancedEditing
?>
