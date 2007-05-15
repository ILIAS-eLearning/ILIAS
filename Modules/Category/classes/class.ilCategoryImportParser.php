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

require_once("./classes/class.ilSaxParser.php");

/**
* Category Import Parser
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesCategory
*/
class ilCategoryImportParser extends ilSaxParser
{
	var $parent;		// current parent ref id
	var $withrol;          // must have value '1' when creating a hierarchy of local roles
 

	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	*
	* @access	public
	*/
	function ilCategoryImportParser($a_xml_file, $a_parent,$withrol)

	{
		$this->parent_cnt = 0;
		$this->parent[$this->parent_cnt] = $a_parent;
		$this->parent_cnt++;
		$this->withrol = $withrol;
		parent::ilSaxParser($a_xml_file);
	}


	/**
	* set event handler
	* should be overwritten by inherited class
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* start the parser
	*/
	function startParsing()
	{
		parent::startParsing();
	}

	/**
	* generate a tag with given name and attributes
	*
	* @param	string		"start" | "end" for starting or ending tag
	* @param	string		element/tag name
	* @param	array		array of attributes
	*/
	function buildTag ($type, $name, $attr="")
	{
		$tag = "<";

		if ($type == "end")
			$tag.= "/";

		$tag.= $name;

		if (is_array($attr))
		{
			while (list($k,$v) = each($attr))
				$tag.= " ".$k."=\"$v\"";
		}

		$tag.= ">";

		return $tag;
	}

	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	  
 	{
	  
	  global $rbacadmin, $rbacreview, $rbacsystem;
	  
	  switch($a_name)
		{
			case "Category":
				$cur_parent = $this->parent[$this->parent_cnt - 1];
				require_once("Modules/Category/classes/class.ilObjCategory.php");
				$this->category = new ilObjCategory;
				$this->category->setImportId($a_attribs["Id"]." (#".$cur_parent.")");
				$this->default_language = $a_attribs["DefaultLanguage"];
				$this->category->setTitle($a_attribs["Id"]);
				$this->category->create();
				$this->category->createReference();
				$this->category->putInTree($cur_parent);
				$this->parent[$this->parent_cnt++] = $this->category->getRefId();
				
				// added for create local roles to categories imported
				if ($this->withrol) {
				    
				  //CHECK ACCESS 'create' rolefolder
				  if (!$rbacsystem->checkAccess('create',$this->category->getRefId(),'rolf')) {
				    $this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolf"),$this->ilias->error_obj->WARNING);
				  }
 
				  include_once ("classes/class.ilObject.php");
				  include_once ("classes/class.ilObjRole.php");
  
				  // create a rolefolder
				  $rolfObj = $this->category->createRoleFolder("Local roles","Role Folder of category obj_no. ".$this->category->getRefId());
				  $parentRoles = $rbacreview->getParentRoleIds($rolfObj->getRefId(),true);
				
				  // iterate through the chosen templates to create a rol for each checkbox checked
				  foreach($_POST["adopt"] as $postadopt) {
					  
					  $desc = $a_attribs["Id"]. " ".$parentRoles[$postadopt]["title"];
					  $roleObj = $rolfObj->createRole($desc,"Local rol for category ".$desc);
					  // adopt permissions from rol template selected
				  	  $rbacadmin->copyRoleTemplatePermissions($postadopt,$parentRoles[$postadopt]["parent"],$rolfObj->getRefId(),$roleObj->getId());					
					  unset($roleObj);
				  }

				  unset($rolfObj);
				  unset($parentRoles);
				  
				  // -----------------------------
				}
				break;

		case "CategorySpec":
		  $this->cur_spec_lang = $a_attribs["Language"];
		  break;

		}

	}


	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser, $a_name)
	{
		global $ilias, $rbacadmin;

		switch($a_name)
		{
			case "Category":
				unset($this->category);
				unset($this->parent[$this->parent_cnt - 1]);
				$this->parent_cnt--;
				break;

			case "CategorySpec":
				$is_def = 0;
				if ($this->cur_spec_lang == $this->default_language)
				{
					$this->category->setTitle($this->cur_title);
					$this->category->setDescription($this->cur_description);
					$this->category->update();
					$is_def = 1;
				}
				$this->category->addTranslation($this->cur_title,
					$this->cur_description, $this->cur_spec_lang, $is_def);
				break;

			case "Title":
				$this->cur_title = $this->cdata;
				break;

			case "Description":
				$this->cur_description = $this->cdata;
				break;
		}

		$this->cdata = "";
	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser, $a_data)
	{
		// i don't know why this is necessary, but
		// the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
		// in character data, but we don't want that, because it's the
		// way we mask user html in our content, so we convert back...
		$a_data = str_replace("<","&lt;",$a_data);
		$a_data = str_replace(">","&gt;",$a_data);

		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			$this->cdata .= $a_data;
		}
	}

}
?>
