<?php

require_once("classes/class.ilObjAICCLearningModuleGUI.php");

/**
* Class ilObjHACPLearningModuleGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjHACPLearningModuleGUI: ilFileSystemGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjHACPLearningModuleGUI extends ilObjAICCLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjHACPLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;
		
		$lng->loadLanguageModule("content");
		$this->type = "hlm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		$this->tabs_gui =& new ilTabsGUI();

	}

	/**
	* display dialogue for importing AICC package
	*
	* @access	public
	*/
	function importObject()
	{
		parent::importObject();
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
			$_GET["ref_id"]."&new_type=hlm"));
		$this->tpl->setVariable("TXT_IMPORT_SLM", $this->lng->txt("import_hlm"));
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
		
		switch ($_HTTP_POST_FILES["scormfile"]["error"])
		{
			case UPLOAD_ERR_INI_SIZE:
				$this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_FORM_SIZE:
				$this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_PARTIAL:
				$this->ilias->raiseError($this->lng->txt("err_partial_file_upload"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_NO_FILE:
				$this->ilias->raiseError($this->lng->txt("err_no_file_uploaded"),$this->ilias->error_obj->MESSAGE);
				break;
		}

		$file = pathinfo($_FILES["scormfile"]["name"]);
		$name = substr($file["basename"], 0,
			strlen($file["basename"]) - strlen($file["extension"]) - 1);
		if ($name == "")
		{
			$name = $this->lng->txt("no_title");
		}

		//$maxFileSize=ini_get('upload_max_filesize');

		// create and insert object in objecttree
		include_once("classes/class.ilObjHACPLearningModule.php");
		$newObj = new ilObjHACPLearningModule();
		//$newObj->setType("alm");
		//$dummy_meta =& new ilMetaData();
		//$dummy_meta->setObject($newObj);
		//$newObj->assignMetaData($dummy_meta);
		$newObj->setTitle($name);
		$newObj->setDescription("");
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		// create data directory, copy file to directory
		$newObj->createDataDirectory();

		// copy uploaded file to data directory
		$file_path = $newObj->getDataDirectory()."/".$_FILES["scormfile"]["name"];
		move_uploaded_file($_FILES["scormfile"]["tmp_name"], $file_path);

		ilUtil::unzip($file_path);


		$cifModule=new ilObjAICCCourseInterchangeFiles();
		$cifModule->findFiles($newObj->getDataDirectory());
		
		$cifModule->readFiles();
		if (!empty($cifModule->errorText)) {
			$this->ilias->raiseError("<b>Error reading LM-File(s):</b><br>".implode("<br>", $cifModule->errorText), $this->ilias->error_obj->WARNING);
		}
		
		if ($_POST["validate"] == "y") {

			$cifModule->validate();
			if (!empty($cifModule->errorText)) {
				$this->ilias->raiseError("<b>Validation Error(s):</b><br>".implode("<br>", $cifModule->errorText), $this->ilias->error_obj->WARNING);
			}
		}
		
		$cifModule->writeToDatabase($newObj->getId());
	}
	

	/**
	* save new learning module to db
	*/
	function saveObject()
	{
		global $rbacadmin;

		$this->uploadObject();

		sendInfo($this->lng->txt("hlm_added"), true);
		ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));

	}

	

} // END class.ilObjAICCLearningModule
?>
