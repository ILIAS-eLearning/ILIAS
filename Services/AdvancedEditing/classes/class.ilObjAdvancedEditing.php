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

require_once "./classes/class.ilObject.php";

/**
* Class ilObjAdvancedEditing
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
*/
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
	* @param string $a_module Name of the module or object which uses the tags
	* @return array HTML tags
	*/
	function &_getUsedHTMLTags($a_module = "")
	{
		$usedtags = array();
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$setting = new ilSetting("advanced_editing");
		$tags = $setting->get("advanced_editing_used_html_tags_" . $a_module);
		if (strlen($tags))
		{
			$usedtags = unserialize($tags);
		}
		else
		{
			// default: everything but tables
			$usedtags = array(
			"a",
			"blockquote",
			"br",
			"cite",
			"code",
			"dd",
			"div",
			"dl",
			"dt",
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
			"u",
			"ul"			
			);
		}
		
		// frm_posts need blockquote and div urgently
		if($a_module === 'frm_post')
		{
			if(!in_array('div', $usedtags))
			{
				$usedtags[] = 'div';
			}
			
			if(!in_array('blockquote', $usedtags))
			{
				$usedtags[] = 'blockquote';
			}
		}
		
		return $usedtags;
	}

	/**
	* Returns a string of all allowed HTML tags for text editing
	*
	* Returns a string of all allowed HTML tags for text editing
	*
	* @param string $a_module Name of the module or object which uses the tags
	* @return string Used HTML tags
	*/
	function &_getUsedHTMLTagsAsString($a_module = "")
	{
		$result = "";
		$tags =& ilObjAdvancedEditing::_getUsedHTMLTags($a_module);
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
	* @param string $a_module The name of the module or object which uses the tags
	* @throws ilAdvancedEditingRequiredTagsException
	* 
	*/
	function _setUsedHTMLTags($a_html_tags, $a_module)
	{
		global $lng;		
		
		if (strlen($a_module))
		{
			$auto_added_tags = array();
			
			// frm_posts need blockquote and div urgently
			if($a_module == 'frm_post')
			{				
				if(!in_array('div', $a_html_tags))
				{
					$auto_added_tags[] = 'div';
				}
				
				if(!in_array('blockquote', $a_html_tags))
				{
					$auto_added_tags[] = 'blockquote';
				}				
			}			
			
			include_once "./Services/Administration/classes/class.ilSetting.php";
			$setting = new ilSetting("advanced_editing");
			$setting->set("advanced_editing_used_html_tags_" . $a_module, serialize(array_merge((array)$a_html_tags, $auto_added_tags)));
			
			if(count($auto_added_tags))
			{
				require_once 'Services/AdvancedEditing/exceptions/class.ilAdvancedEditingRequiredTagsException.php';
				throw new ilAdvancedEditingRequiredTagsException(
					sprintf(
						$lng->txt('advanced_editing_required_tags'),
						implode(', ', $auto_added_tags)
					)
				);
			}
		}
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
			"dd",
			"div",
			"dl",
			"dt",
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
			"object",
			"ol",
			"p",
			"param",
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
			"ul",
			"ruby", // Ruby Annotation XHTML module
			"rbc",
			"rtc",
			"rb",
			"rt",
			"rp"
		);
		return $tags;
	}

	/**
	* Returns an array of all possible HTML tags for text editing
	*
	* Returns an array of all possible HTML tags for text editing
	*
	* @return array HTML tags
	*/
	function &_getAllHTMLTags()
	{
		$tags = array(
			"a",
			"abbr",
			"acronym",
			"address",
			"applet",
			"area",
			"b",
			"base",
			"basefont",
			"bdo",
			"big",
			"blockquote",
			"br",
			"button",
			"caption",
			"center",
			"cite",
			"code",
			"col",
			"colgroup",
			"dd",
			"del",
			"dfn",
			"dir",
			"div",
			"dl",
			"dt",
			"em",
			"fieldset",
			"font",
			"form",
			"h1",
			"h2",
			"h3",
			"h4",
			"h5",
			"h6",
			"hr",
			"i",
			"iframe",
			"img",
			"input",
			"ins",
			"isindex",
			"kbd",
			"label",
			"legend",
			"li",
			"link",
			"map",
			"menu",
			"object",
			"ol",
			"optgroup",
			"option",
			"p",
			"param",
			"pre",
			"q",
			"s",
			"samp",
			"select",
			"small",
			"span",
			"strike",
			"strong",
			"sub",
			"sup",
			"table",
			"tbody",
			"td",
			"textarea",
			"tfoot",
			"th",
			"thead",
			"tr",
			"tt",
			"u",
			"ul",
			"var",
			"ruby", // Ruby Annotation XHTML module
			"rbc",
			"rtc",
			"rb",
			"rt",
			"rp"
			);
			return $tags;
		}
		/**
		* Returns a string of all HTML tags
		*
		* Returns a string of all HTML tags
		*
		* @return string Used HTML tags
		*/
		function _getAllHTMLTagsAsString()
		{
			$result = "";
			$tags =& ilObjAdvancedEditing::_getAllHTMLTags();
			foreach ($tags as $tag)
			{
				$result .= "<$tag>";
			}
			return $result;
		}
	
	/**
	* Sets the state of the rich text editor visibility for the current user
	*
	* Sets the state of the rich text editor visibility for the current user
	*
	* @param integer $a_state 0 if the RTE should be disabled, 1 otherwise
	*/
	function _setRichTextEditorUserState($a_state)
	{
		global $ilUser;
		$ilUser->writePref("show_rte", $a_state);
	}

	/**
	* Gets the state of the rich text editor visibility for the current user
	*
	* Gets the state of the rich text editor visibility for the current user
	*
	* @return integer 0 if the RTE should be disabled, 1 otherwise
	*/
	function _getRichTextEditorUserState()
	{
		global $ilUser;
		if (strlen($ilUser->getPref("show_rte")) > 0)
		{
			return $ilUser->getPref("show_rte");
		}
		return 1;
	}
	
} // END class.ilObjAdvancedEditing
?>
