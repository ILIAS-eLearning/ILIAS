<?php
/**
* Class LearningModuleObjectOut
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* $Id$Id: class.LearningModuleObjectOut.php,v 1.13 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends ObjectOut
* @package ilias-core
*/

class LearningModuleObjectOut extends ObjectOut
{
	/**
	* Constructor
	*
	* @access public
	*/
	function LearningModuleObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "le";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}


	function viewObject()
	{
		global $rbacsystem, $tree, $tpl;

		$lotree = new Tree($_GET["ref_id"],ROOT_FOLDER_ID,$_GET["ref_id"]);
		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "view", "title", "description", "last_change");

		if ($lotree->getChilds($_GET["obj_id"], $_GET["order"], $_GET["direction"]))
		{
			foreach ($lotree->Childs as $key => $val)
		    {
				// visible
				//if (!$rbacsystem->checkAccess("visible",$val["id"],$val["parent"]))
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
					"obj_id" => $_GET["obj_id"],
					"parent" => $_GET["parent"],
					"parent_parent" => $val["parent_parent"],
					"lm_id" => $_GET["obj_id"],
					"lo_id" => $val["id"]
				);

		    } //foreach
		} //if
		parent::displayList();
	}


	/**
	* Overwritten method from class.Object.php
	* It handles all button commands from Learning Modules
	*
	* @access public
	*/
	function gatewayObject()
	{
		global $lng;

		switch(key($_POST["cmd"]))
		{
			case "import":
				return $this->importObject();
				break;

			case "export":
				return;
				break;

			case "upload":
				return $this->uploadObject();
				break;
		}
		parent::gatewayObject();
	}

	/**
	* display dialogue for importing XML-LeaningObjects
	*
	*  @access	public
	*/
	function importObject()
	{
		$this->getTemplateFile("import");
		$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=gateway&type=".$_GET["type"].
						  "&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"]);
		$this->tpl->setVariable("BTN_NAME", "upload");
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_PARSE", $this->lng->txt("parse"));
		$this->tpl->setVariable("TXT_VALIDATE", $this->lng->txt("validate"));
		$this->tpl->setVariable("TXT_SELECT_MODE", $this->lng->txt("select_mode"));
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
		global $HTTP_POST_FILES;

		require_once "classes/class.LearningModuleObject.php";

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

		//
		$lmObj = new LearningModuleObject($_GET["obj_id"]);
		$this->data = $lmObj->upload(	$_POST["parse_mode"],
										$HTTP_POST_FILES["xmldoc"]["tmp_name"],
										$HTTP_POST_FILES["xmldoc"]["name"]);
		unset($lmObj);


		header("Location: adm_object.php?cmd=view&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"].
			   "&message=".urlencode($this->data["msg"]));
		exit();

		//nada para mirar ahora :-)
	}
} // END class.LeraningObject
?>