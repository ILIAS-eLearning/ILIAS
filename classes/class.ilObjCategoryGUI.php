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
* Class ilObjCategoryGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @author Sascha Hofmann <shofmann@databay.de> 
* $Id$Id: class.ilObjCategoryGUI.php,v 1.5 2003/07/15 08:23:56 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjCategoryGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjCategoryGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "cat";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
	
	/**
	* create new category form
	*
	* @access	public
	*/
	function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];
		
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			// for lang selection include metadata class
			include_once "./classes/class.ilMetaData.php";

			$this->getTemplateFile("edit",$new_type);
			
			$array_push = true;

			if ($_SESSION["error_post_vars"])
			{
				$_SESSION["translation_post"] = $_SESSION["error_post_vars"];
				$array_push = false;
			}

			// clear session data if a fresh category should be created
			if (($_GET["mode"] != "session"))
			{
				unset($_SESSION["translation_post"]);
			}	// remove a translation from session
			elseif ($_GET["entry"] != 0)
			{
				array_splice($_SESSION["translation_post"]["Fobject"],$_GET["entry"],1,array());
				
				if ($_GET["entry"] == $_SESSION["translation_post"]["default_language"])
				{
					$_SESSION["translation_post"]["default_language"] = "";
				}
			}
			
			$data = $_SESSION["translation_post"];
			
			if (!is_array($data["Fobject"]))
			{
				$data["Fobject"] = array();
			}		

			// add additional translation form
			if (!$_GET["entry"] and $array_push)
			{
				$count = array_push($data["Fobject"],array("title" => "","desc" => ""));
			}
			else
			{
				$count = count($data["Fobject"]);
			}

			foreach ($data["Fobject"] as $key => $val)
			{
				// add translation button
				if ($key == $count -1)
				{
					$this->tpl->setCurrentBlock("addTranslation");
					$this->tpl->setVariable("TXT_ADD_TRANSLATION",$this->lng->txt("add_translation")." >>");
					$this->tpl->parseCurrentBlock();
				}
				
				// remove translation button
				if ($key != 0)
				{
					$this->tpl->setCurrentBlock("removeTranslation");
					$this->tpl->setVariable("TXT_REMOVE_TRANSLATION",$this->lng->txt("remove_translation"));
					$this->tpl->setVariable("LINK_REMOVE_TRANSLATION", "adm_object.php?cmd=removeTranslation&entry=".$key."&mode=create&ref_id=".$_GET["ref_id"]."&new_type=".$new_type);
					$this->tpl->parseCurrentBlock();
				}
				
				// lang selection
				$this->tpl->addBlockFile("SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html", false);
				$this->tpl->setVariable("SEL_NAME", "Fobject[".$key."][lang]");
	
				$languages = ilMetaData::getLanguages();
	
				foreach($languages as $code => $language)
				{
					$this->tpl->setCurrentBlock("lg_option");
					$this->tpl->setVariable("VAL_LG", $code);
					$this->tpl->setVariable("TXT_LG", $language);
		
					if ($count == 1 AND $code == $this->ilias->account->getPref("language"))
					{
						$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
					}
					elseif ($code == $val["lang"])
					{
						$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
					}
	
					$this->tpl->parseCurrentBlock();
				}
				
				// object data
				$this->tpl->setCurrentBlock("obj_form");
				
				if ($key == 0)
				{
					$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
				}
				else
				{
					$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("translation")." ".$key);
				}
				
				if ($key == $data["default_language"])
				{
					$this->tpl->setVariable("CHECKED", "checked=\"checked\"");
				}
		
				$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
				$this->tpl->setVariable("TXT_DEFAULT", $this->lng->txt("default"));
				$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
				$this->tpl->setVariable("TITLE", $val["title"]);
				$this->tpl->setVariable("DESC", $val["desc"]);
				$this->tpl->setVariable("NUM", $key);
				$this->tpl->parseCurrentBlock();
			}

			// global
			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&mode=create&ref_id=".$_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}
	
	/**
	* save category
	* @access	public
	*/
	function saveObject()
	{
		$data = $_POST;
		
		// default language set?
		if (!isset($data["default_language"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_default_language"),$this->ilias->error_obj->MESSAGE);
		}
		
		// prepare array fro further checks
		foreach ($data["Fobject"] as $key => $val)
		{
			$langs[$key] = $val["lang"];
		}
		
		$langs = array_count_values($langs);
		
		// all languages set?
		if (array_key_exists("",$langs))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_language_selected"),$this->ilias->error_obj->MESSAGE);
		}
		
		// no single language is selected more than once?
		if (array_sum($langs) > count($langs))
		{
			$this->ilias->raiseError($this->lng->txt("msg_multi_language_selected"),$this->ilias->error_obj->MESSAGE);
		}
		
		// copy default translation to variable for object data entry
		$_POST["Fobject"]["title"] = $_POST["Fobject"][$_POST["default_language"]]["title"];
		$_POST["Fobject"]["desc"] = $_POST["Fobject"][$_POST["default_language"]]["desc"];

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles if needed (see ilObjForum & ilObjForumGUI for an example)
		//$roles = $newObj->initDefaultRoles();

		// write translations to object_translation
		foreach ($data["Fobject"] as $key => $val)
		{
			if ($key == $data["default_language"])
			{
				$default = 1;
			}
			else
			{
				$default = 0;
			}
			
			$newObj->setTitle($val["title"]);
			$newObj->setDescription($val["desc"]);
			
			$q = "INSERT INTO object_translation ".
				 "(obj_id,title,description,lang_code,lang_default) ".
				 "VALUES ".
				 "(".$newObj->getId().",'".$newObj->getTitle()."','".$newObj->getDescription()."','".$val["lang"]."',".$default.")";
			$this->ilias->db->query($q);
		}

		// always send a message
		sendInfo($this->lng->txt("cat_added"),true);
		
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}
	
	/**
	* edit category
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			// for lang selection include metadata class
			include_once "./classes/class.ilMetaData.php";

			$this->getTemplateFile("edit",$new_type);
			
			$array_push = true;

			if ($_SESSION["error_post_vars"])
			{
				$_SESSION["translation_post"] = $_SESSION["error_post_vars"];
				$_GET["mode"] = "session";
				$array_push = false;
			}
			
			// load from db if edit category is called the first time
			if (($_GET["mode"] != "session"))
			{
				$data = $this->object->getTranslations();
				$_SESSION["translation_post"] = $data;
				$array_push = false;
			}	// remove a translation from session
			elseif ($_GET["entry"] != 0)
			{
				array_splice($_SESSION["translation_post"]["Fobject"],$_GET["entry"],1,array()); 

				if ($_GET["entry"] == $_SESSION["translation_post"]["default_language"])
				{
					$_SESSION["translation_post"]["default_language"] = "";
				}
			}

			$data = $_SESSION["translation_post"];

			// add additional translation form
			if (!$_GET["entry"] and $array_push)
			{
				$count = array_push($data["Fobject"],array("title" => "","desc" => ""));
			}
			else
			{
				$count = count($data["Fobject"]);
			}

			foreach ($data["Fobject"] as $key => $val)
			{
				// add translation button
				if ($key == $count -1)
				{
					$this->tpl->setCurrentBlock("addTranslation");
					$this->tpl->setVariable("TXT_ADD_TRANSLATION",$this->lng->txt("add_translation")." >>");
					$this->tpl->parseCurrentBlock();
				}
				
				// remove translation button
				if ($key != 0)
				{
					$this->tpl->setCurrentBlock("removeTranslation");
					$this->tpl->setVariable("TXT_REMOVE_TRANSLATION",$this->lng->txt("remove_translation"));
					$this->tpl->setVariable("LINK_REMOVE_TRANSLATION", "adm_object.php?cmd=removeTranslation&entry=".$key."&mode=edit&ref_id=".$_GET["ref_id"]);
					$this->tpl->parseCurrentBlock();
				}
				
				// lang selection
				$this->tpl->addBlockFile("SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html", false);
				$this->tpl->setVariable("SEL_NAME", "Fobject[".$key."][lang]");
	
				$languages = ilMetaData::getLanguages();
	
				foreach($languages as $code => $language)
				{
					$this->tpl->setCurrentBlock("lg_option");
					$this->tpl->setVariable("VAL_LG", $code);
					$this->tpl->setVariable("TXT_LG", $language);
		
					if ($count == 1 AND $code == $this->ilias->account->getPref("language"))
					{
						$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
					}
					elseif ($code == $val["lang"])
					{
						$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
					}
	
					$this->tpl->parseCurrentBlock();
				}
				
				// object data
				$this->tpl->setCurrentBlock("obj_form");
				
				if ($key == 0)
				{
					$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
				}
				else
				{
					$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("translation")." ".$key);
				}
				
				if ($key == $data["default_language"])
				{
					$this->tpl->setVariable("CHECKED", "checked=\"checked\"");
				}
		
				$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
				$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
				$this->tpl->setVariable("TXT_DEFAULT", $this->lng->txt("default"));
				$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
				$this->tpl->setVariable("TITLE", $val["title"]);
				$this->tpl->setVariable("DESC", $val["desc"]);
				$this->tpl->setVariable("NUM", $key);
				$this->tpl->parseCurrentBlock();
			}

			// global
			$this->tpl->setVariable("FORMACTION", $this->getFormAction("update","adm_object.php?cmd=gateway&mode=edit&ref_id=".$_GET["ref_id"]));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
			$this->tpl->setVariable("CMD_SUBMIT", "update");
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}

	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	function updateObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$data = $_POST;
		
			// default language set?
			if (!isset($data["default_language"]))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_default_language"),$this->ilias->error_obj->MESSAGE);
			}
			
			// prepare array fro further checks
			foreach ($data["Fobject"] as $key => $val)
			{
				$langs[$key] = $val["lang"];
			}
			
			$langs = array_count_values($langs);
			
			// all languages set?
			if (array_key_exists("",$langs))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_language_selected"),$this->ilias->error_obj->MESSAGE);
			}

			// no single language is selected more than once?
			if (array_sum($langs) > count($langs))
			{
				$this->ilias->raiseError($this->lng->txt("msg_multi_language_selected"),$this->ilias->error_obj->MESSAGE);
			}

			// copy default translation to variable for object data entry
			$_POST["Fobject"]["title"] = $_POST["Fobject"][$_POST["default_language"]]["title"];
			$_POST["Fobject"]["desc"] = $_POST["Fobject"][$_POST["default_language"]]["desc"];

			// first delete all translation entries...
			$q = "DELETE FROM object_translation WHERE obj_id= ".$this->object->getId();
			$this->ilias->db->query($q);
			
			// ...and write new translations to object_translation
			foreach ($data["Fobject"] as $key => $val)
			{
				if ($key == $data["default_language"])
				{
					$default = 1;
				}
				else
				{
					$default = 0;
				}
				
				$this->object->setTitle($val["title"]);
				$this->object->setDescription($val["desc"]);
				
				$q = "INSERT INTO object_translation ".
					 "(obj_id,title,description,lang_code,lang_default) ".
					 "VALUES ".
					 "(".$this->object->getId().",'".$this->object->getTitle()."','".$this->object->getDescription()."','".$val["lang"]."',".$default.")";
				$this->ilias->db->query($q);
			}

			// update object data entry with default translation
			$this->object->setTitle($_POST["Fobject"]["title"]);
			$this->object->setDescription($_POST["Fobject"]["desc"]);
			$this->update = $this->object->update();
		}

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		
		header("Location: adm_object.php?ref_id=".$this->object->getRefId());
		exit();
	}
	
	/**
	* adds a translation form & save post vars to session
	* 
	* @access	public
	*/
	function addTranslationObject()
	{
		global $log;

		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::addTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$_SESSION["translation_post"] = $_POST;
		
		header("location: adm_object.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$_GET["ref_id"]."&new_type=".$_GET["new_type"]);
		exit();	
	}

	/**
	* removes a translation form & save post vars to session
	* 
	* @access	public
	*/
	function removeTranslationObject()
	{
		global $log;
		
		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::removeTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		header("location: adm_object.php?cmd=".$_GET["mode"]."&entry=".$_GET["entry"]."&mode=session&ref_id=".$_GET["ref_id"]."&new_type=".$_GET["new_type"]);
		exit();	
	}
} // END class.ilObjCategoryGUI
?>
