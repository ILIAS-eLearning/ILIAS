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

require_once "classes/class.ilObjectGUI.php";

/**
* Class ilObjSCORMLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjSCORMLearningModuleGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSCORMLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "slm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
	}

	function viewObject()
	{
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK","content/scorm_presentation.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* no manual SCORM creation, only import at the time
	*/
	function createObject()
	{
		$this->importObject();
	}

	/**
	* display dialogue for importing SCORM package
	*
	* @access	public
	*/
	function importObject()
	{
		// display import form
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.slm_import.html");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?&ref_id=".$_GET["ref_id"]."&cmd=gateway&new_type=slm");
		$this->tpl->setVariable("BTN_NAME", "upload");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_IMPORT_SLM", $this->lng->txt("import_slm"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
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

		// check if file was uploaded
		$source = $HTTP_POST_FILES["scormfile"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}
		// check create permission
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $_GET["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
		}

		$file = pathinfo($_FILES["scormfile"]["name"]);
		$name = substr($file["basename"], 0,
			strlen($file["basename"]) - strlen($file["extension"]) - 1);
		if ($name == "")
		{
			$name = $this->lng->txt("no_title");
		}

		// create and insert object in objecttree
		include_once("classes/class.ilObjSCORMLearningModule.php");
		$newObj = new ilObjSCORMLearningModule();
		$newObj->setType("slm");
		$newObj->setTitle($name);
		$newObj->setDescription("");
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);

		// create data directory, copy file to directory
		$newObj->createDataDirectory();

		// copy uploaded file to data directory
		$file_path = $newObj->getDataDirectory()."/".$_FILES["scormfile"]["name"];
		move_uploaded_file($_FILES["scormfile"]["tmp_name"], $file_path);

		ilUtil::unzip($file_path);

		//validate the XML-Files in the SCORM-Package
		if (!$newObj->validate($newObj->getDataDirectory()))
		{
			$this->ilias->raiseError("<b>Validation Error(s):</b><br>".$newObj->getValidationSummary(), $this->ilias->error_obj->WARNING);
		}

		// start SCORM package parser
		include_once ("content/classes/SCORM/class.ilSCORMPackageParser.php");
		// todo determine imsmanifest.xml path here...
		$manifest_file = $newObj->getDataDirectory()."/imsmanifest.xml";
		$slmParser = new ilSCORMPackageParser($newObj, $manifest_file);
		$slmParser->startParsing();

		header("Location: adm_object.php?".$this->link_params);
		exit();
	}

	/**
	* save new learning module to db
	*/
	function saveObject()
	{
		global $rbacadmin;

		// always call parent method first to create an object_data entry & a reference
		// $newObj = parent::saveObject();
		// TODO: fix MetaDataGUI implementation to make it compatible to use parent call 

		// create and insert object in objecttree
		include_once("classes/class.ilObjSCORMLearningModule.php");
		$newObj = new ilObjLearningModule();
		$newObj->setType("lm");
		$newObj->setTitle("dummy");			// set by meta_gui->save
		$newObj->setDescription("dummy");	// set by meta_gui->save
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["ref_id"]);
		
		// save meta data
		include_once "classes/class.ilMetaDataGUI.php";
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($newObj);
		$meta_gui->save();

		// create learning module tree
		$newObj->createLMTree();

		unset($newObj);

		// always send a message
		sendInfo($this->lng->txt("slm_added"),true);
		
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}
} // END class.ilObjSCORMLearningModule
?>
