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
* Class ilObjLearningModuleGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* $Id$Id: class.ilObjLearningModuleGUI.php,v 1.22 2003/07/13 09:08:09 akill Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "classes/class.ilObjectGUI.php";

class ilObjLearningModuleGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "lm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

	}

	/**
	* form for new lm creation
	*/
	function createObject()
	{
		require_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		//$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			"adm_object.php?ref_id=".$_GET["ref_id"]."&new_type=".$_POST["new_type"]."&cmd=save");
	}

	/**
	* save new learning module to db
	*/
	function saveObject()
	{
		global $rbacadmin;

		// always call parent method first to create an object_data entry & a reference
		//$newObj = parent::saveObject();
		// TODO: fix MetaDataGUI implementation to make it compatible to use parent call 


		// create and insert object in objecttree
		include_once("classes/class.ilObjLearningModule.php");
		$newObj = new ilObjLearningModule();
		$newObj->setType("lm");
		$newObj->setTitle("dummy");			// set by meta_gui->save
		$newObj->setDescription("dummy");	// set by meta_gui->save
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);

		// save meta data
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($newObj);
		$meta_gui->save();

		// create learning module tree
		$newObj->createLMTree();

		unset($newObj);

		// always send a message
		sendInfo($this->lng->txt("lm_added"),true);
		
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}

	/**
	* display dialogue for importing XML-LeaningObjects
	*
	* @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import", "lm");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?&ref_id=".$_GET["ref_id"]."&cmd=gateway&new_type=lm");
		$this->tpl->setVariable("BTN_NAME", "upload");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_IMPORT_LM", $this->lng->txt("import_lm"));
		$this->tpl->setVariable("TXT_PARSE", $this->lng->txt("parse"));
		$this->tpl->setVariable("TXT_VALIDATE", $this->lng->txt("validate"));
		$this->tpl->setVariable("TXT_PARSE2", $this->lng->txt("parse2"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));

	}


	function editMetaObject()
	{
		require_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			"adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
	}

	function saveMetaObject()
	{
		require_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save();
		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]);
		exit;
	}



	/**
	* view object
	*
	* @access	public
	*/
	function viewObject()
	{
		global $rbacsystem, $tree, $tpl;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","content/lm_edit.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit"));
		$this->tpl->parseCurrentBlock();

		// view button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","content/lm_presentation.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"_top\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();

		if ($this->object->getStyleSheetId() == 0)
		{
		}


		$lotree = new ilTree($_GET["ref_id"],ROOT_FOLDER_ID);

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "view", "title", "description", "last_change");

		$lo_childs = $lotree->getChilds($_GET["ref_id"], $_GET["order"], $_GET["direction"]);

		foreach ($lo_childs as $key => $val)
		{
			// visible
			//if (!$rbacsystem->checkAccess("visible",$val["id"]))
			//{
			//	continue;
			//}
			//visible data part
			$this->data["data"][] = array(
					"type" => "<img src=\"".$this->tpl->tplPath."/images/enlarge.gif\" border=\"0\">",
					"title" => $val["title"],
					"description" => $val["desc"],
					"last_change" => $val["last_update"]
				);

			//control information
			$this->data["ctrl"][] = array(
					"type" => $val["type"],
					"ref_id" => $_GET["ref_id"],
					"lm_id" => $_GET["obj_id"],
					"lo_id" => $val["child"]
				);
	    } //foreach

		parent::displayList();
	}

	/**
	* export object
	*
	* @access	public
	*/
	function exportObject()
	{
		return;
	}


	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject()
	{
		global $HTTP_POST_FILES, $rbacsystem;

		require_once "classes/class.ilObjLearningModule.php";

		// check if file was uploaded
		$source = $HTTP_POST_FILES["xmldoc"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}
		// check create permission
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
		}

		// check correct file type
		if ($HTTP_POST_FILES["xmldoc"]["type"] != "application/zip")
		{
			$this->ilias->raiseError("Wrong file type!",$this->ilias->error_obj->MESSAGE);
		}

		// create and insert object in objecttree
		require_once("classes/class.ilObjLearningModule.php");
		$newObj = new ilObjLearningModule();
		$newObj->setType("lm");
		$newObj->setTitle("dummy");			// set by meta_gui->save
		$newObj->setDescription("dummy");	// set by meta_gui->save
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);

		// create learning module tree
		$newObj->createLMTree();

		// --- start: test of alternate parsing / lm storing
		if ($_POST["parse_mode"] == 2)
		{
			// create import directory
			$newObj->createImportDirectory();

			// copy uploaded file to import directory
			$file = pathinfo($_FILES["xmldoc"]["name"]);
			$full_path = $newObj->getImportDirectory()."/".$_FILES["xmldoc"]["name"];
			move_uploaded_file($_FILES["xmldoc"]["tmp_name"], $full_path);

			// unzip file
			$cdir = getcwd();
			chdir($newObj->getImportDirectory());
			$unzip = $this->ilias->getSetting("unzip_path");
			$unzipcmd = $unzip." ".$file["basename"];
//echo "unzipcmd :".$unzipcmd.":<br>";
			exec($unzipcmd);
			chdir($cdir);

			// determine filename of xml file
			$subdir = basename($file["basename"],".".$file["extension"]);
			$xml_file = $newObj->getImportDirectory()."/".$subdir."/".$subdir.".xml";
//echo "xmlfile:".$xml_file;

			require_once ("content/classes/class.ilLMParser.php");
			$lmParser = new ilLMParser($newObj, $xml_file, $subdir);
			$lmParser->startParsing();
		} // --- end: test of alternate parsing / lm storing
		else
		{
			// original import
			$this->data = $newObj->upload(	$_POST["parse_mode"],
											$HTTP_POST_FILES["xmldoc"]["tmp_name"],
											$HTTP_POST_FILES["xmldoc"]["name"]);
			unset($newObj);
		}

		header("Location: adm_object.php?".$this->link_params);
		exit();

		//nada para mirar ahora :-)
	}
} // END class.ilObjLearningModuleGUI
?>
