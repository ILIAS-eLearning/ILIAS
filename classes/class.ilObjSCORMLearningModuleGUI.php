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

		// create and insert object in objecttree
		require_once("classes/class.ilObjSCORMLearningModule.php");
		$newObj = new ilObjSCORMLearningModule();
		$newObj->setType("slm");
		$newObj->setTitle("temp title");				// should be set by
		$newObj->setDescription("temp description");	// import parser
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);

		// todo: create directory, copy file to directory

		// start SCORM package parser
		require_once ("content/classes/SCORM/class.ilSCORMPackageParser.php");
		$slmParser = new ilSCORMPackageParser($newObj, $HTTP_POST_FILES["scormfile"]["tmp_name"]);
		$slmParser->startParsing();

		header("Location: adm_object.php?".$this->link_params);
		exit();
	}



}
?>
