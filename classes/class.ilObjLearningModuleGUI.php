<?php
/**
* Class ilObjLearningModuleGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* $Id$Id: class.ilObjLearningModuleGUI.php,v 1.9 2003/04/29 07:33:18 akill Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjLearningModuleGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjLearningModuleGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "le";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
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
	* display dialogue for importing XML-LeaningObjects
	*
	* @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?&ref_id=".$_GET["ref_id"]."&cmd=gateway");
		$this->tpl->setVariable("BTN_NAME", "upload");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_IMPORT_LM", $this->lng->txt("import_lm"));
		$this->tpl->setVariable("TXT_PARSE", $this->lng->txt("parse"));
		$this->tpl->setVariable("TXT_VALIDATE", $this->lng->txt("validate"));
		$this->tpl->setVariable("TXT_PARSE2", $this->lng->txt("parse2"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));

	}

	/**
	* test implementation, will be moved or deleted
	*/
	function view2Object()
	{
		header("Location: content/lm_presentation.php?lm_id=".$this->object->getID());
	}

	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject()
	{
		global $HTTP_POST_FILES;

		require_once "classes/class.ilObjLearningModule.php";

		// check if file was uploaded
		$source = $HTTP_POST_FILES["xmldoc"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}

		// check correct file type
		if ($HTTP_POST_FILES["xmldoc"]["type"] != "text/xml")
		{
			$this->ilias->raiseError("Wrong file type!",$this->ilias->error_obj->MESSAGE);
		}

		// --- start: test of alternate parsing / lm storing
		if ($_POST["parse_mode"] == 2)
		{
			require_once ("content/classes/class.ilLMParser.php");
			$lmParser = new ilLMParser($this->object->getID(), $HTTP_POST_FILES["xmldoc"]["tmp_name"]);
			$lmParser->startParsing();
			exit;
		}
		// --- end: test of alternate parsing / lm storing

		//
		$lmObj = new ilObjLearningModule($_GET["ref_id"]);
		$this->data = $lmObj->upload(	$_POST["parse_mode"],
										$HTTP_POST_FILES["xmldoc"]["tmp_name"],
										$HTTP_POST_FILES["xmldoc"]["name"]);
		unset($lmObj);


		header("Location: adm_object.php?ref_id=".$_GET["ref_id"]."&message=".urlencode($this->data["msg"]));
		exit();

		//nada para mirar ahora :-)
	}
} // END class.LeraningObject
?>
