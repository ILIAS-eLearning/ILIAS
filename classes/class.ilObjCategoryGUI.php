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
* $Id$Id: class.ilObjCategoryGUI.php,v 1.16 2004/04/12 13:46:52 shofmann Exp $
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
	function ilObjCategoryGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "cat";
		$this->ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
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

			//add template for buttons
			$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK", "adm_object.php?ref_id=".$this->ref_id."&cmd=importCategoriesForm");
			$this->tpl->setVariable("BTN_TXT", $this->lng->txt("import_categories"));
			$this->tpl->parseCurrentBlock();

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

			// stripslashes in form output?
			$strip = isset($_SESSION["translation_post"]) ? true : false;

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

					if ($count == 1 AND $code == $this->ilias->account->getPref("language") AND !isset($_SESSION["translation_post"]))
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
				$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($val["title"],$strip));
				$this->tpl->setVariable("DESC", ilUtil::stripSlashes($val["desc"]));
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

			$newObj->addTranslation(ilUtil::stripSlashes($val["title"]),ilUtil::stripSlashes($val["desc"]),$val["lang"],$default);
		}

		// always send a message
		sendInfo($this->lng->txt("cat_added"),true);
		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
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

		// stripslashes in form?
		$strip = isset($_SESSION["translation_post"]) ? true : false;

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

			foreach ($languages as $code => $language)
			{
				$this->tpl->setCurrentBlock("lg_option");
				$this->tpl->setVariable("VAL_LG", $code);
				$this->tpl->setVariable("TXT_LG", $language);

				if ($code == $val["lang"])
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
			$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($val["title"],$strip));
			$this->tpl->setVariable("DESC", ilUtil::stripSlashes($val["desc"]));
			$this->tpl->setVariable("NUM", $key);
			$this->tpl->parseCurrentBlock();
		}

		// global
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update","adm_object.php?cmd=gateway&mode=edit&ref_id=".$_GET["ref_id"]));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
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
			$this->object->removeTranslations();

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

				$this->object->addTranslation(ilUtil::stripSlashes($val["title"]),ilUtil::stripSlashes($val["desc"]),$val["lang"],$default);
			}

			// update object data entry with default translation
			$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
			$this->update = $this->object->update();
		}

		sendInfo($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->getReturnLocation("update","adm_object.php?".$this->link_params));
	}

	/**
	* adds a translation form & save post vars to session
	*
	* @access	public
	*/
	function addTranslationObject()
	{
		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::addTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		$_SESSION["translation_post"] = $_POST;
		ilUtil::redirect($this->getReturnLocation("addTranslation",
			"adm_object.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$_GET["ref_id"]."&new_type=".$_GET["new_type"]));
	}

	/**
	* removes a translation form & save post vars to session
	*
	* @access	public
	*/
	function removeTranslationObject()
	{
		if (!($_GET["mode"] != "create" or $_GET["mode"] != "edit"))
		{
			$message = get_class($this)."::removeTranslationObject(): Missing or wrong parameter! mode: ".$_GET["mode"];
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		ilUtil::redirect("adm_object.php?cmd=".$_GET["mode"]."&entry=".$_GET["entry"]."&mode=session&ref_id=".$_GET["ref_id"]."&new_type=".$_GET["new_type"]);
	}

	/**
	* display form for category import
	*/
	function importCategoriesFormObject ()
	{
		/*$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.cat_import_form.html");

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."&cmd=gateway");

		$this->tpl->setVariable("TXT_IMPORT_CATEGORIES", $this->lng->txt("import_categories"));
		$this->tpl->setVariable("TXT_IMPORT_FILE", $this->lng->txt("import_file"));

		$this->tpl->setVariable("BTN_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));*/
		ilObjCategoryGUI::_importCategoriesForm($this->ref_id, $this->tpl);
	}

	/**
	* display form for category import (static, also called by RootFolderGUI)
	*/
	function _importCategoriesForm ($a_ref_id, &$a_tpl)
	{
		global $lng;

		$a_tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.cat_import_form.html");

		$a_tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$a_ref_id."&cmd=gateway");

		$a_tpl->setVariable("TXT_IMPORT_CATEGORIES", $lng->txt("import_categories"));
		$a_tpl->setVariable("TXT_IMPORT_FILE", $lng->txt("import_file"));

		$a_tpl->setVariable("BTN_IMPORT", $lng->txt("import"));
		$a_tpl->setVariable("BTN_CANCEL", $lng->txt("cancel"));
	}


	/**
	* import cancelled
	*
	* @access private
	*/
	function importCancelledObject()
	{
		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect("adm_object.php?ref_id=".$_GET["ref_id"]);
	}

	/**
	* get user import directory name
	*/
	function _getImportDir()
	{
		return ilUtil::getDataDir()."/cat_import";
	}

	/**
	* import categories
	*/
	function importCategoriesObject()
	{
		ilObjCategoryGUI::_importCategories($_GET["ref_id"]);
	}

	/**
	* import categories (static, also called by RootFolderGUI)
	*/
	function _importCategories($a_ref_id)
	{
		global $lng;

		require_once("classes/class.ilCategoryImportParser.php");

		$import_dir = ilObjCategoryGUI::_getImportDir();

		// create user import directory if necessary
		if (!@is_dir($import_dir))
		{
			ilUtil::createDirectory($import_dir);
		}

		// move uploaded file to user import directory
		$file_name = $_FILES["importFile"]["name"];
		$parts = pathinfo($file_name);
		$full_path = $import_dir."/".$file_name;
		move_uploaded_file($_FILES["importFile"]["tmp_name"], $full_path);

		// unzip file
		ilUtil::unzip($full_path);

		$subdir = basename($parts["basename"],".".$parts["extension"]);
		$xml_file = $import_dir."/".$subdir."/".$subdir.".xml";

		$importParser = new ilCategoryImportParser($xml_file, $a_ref_id);
		$importParser->startParsing();

		sendInfo($lng->txt("categories_imported"), true);
		ilUtil::redirect("adm_object.php?ref_id=".$a_ref_id);
	}
} // END class.ilObjCategoryGUI
?>
