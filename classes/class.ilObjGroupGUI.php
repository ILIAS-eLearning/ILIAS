<?php
/**
* Class ilObjGroupGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjGroupGUI.php,v 1.3 2003/04/17 12:55:23 mmaschke Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";
require_once "class.ilObjGroup.php";

class ilObjGroupGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjGroupGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "grp";
		//$this->lng =& $lng;
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
	
	/**
	* create new object form
	*/
	function createObject()
	{
		//TODO: check the acces rights; compare class.ilObjectGUI.php
		
		global $rbacsystem;

			$data = array();
			$data["fields"] = array();
			$data["fields"]["group_name"] = "";
			$data["fields"]["desc"] = "";
			

			$this->getTemplateFile("new","group");

			foreach ($data["fields"] as $key => $val)
			{  
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
				$this->tpl->parseCurrentBlock();
			}
			
			$stati = array("group_status_public","group_status_private","group_status_closed");
			
			//build form
			$opts = ilUtil::formSelect(0,"group_status_select",$stati);

			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("TXT_GROUP_STATUS", $this->lng->txt("group_status"));
			$this->tpl->setVariable("FORMACTION", "adm_object.php?cmd=save"."&ref_id=".$_GET["ref_id"].
				"&new_type=".$_POST["new_type"]);
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	}
	
	
	/**
	* save Object
	* @access public
	*/
	function saveObject()
	{	
		//TODO: check the acces rights; compare class.ilObjectGUI.php
		global $rbacadmin,$ilias;
		
		$newObj = new ilObject();
		$newObj->setType("grp");
		$newObj->setTitle($_POST["Fobject"]["title"]);
		$newObj->setDescription($_POST["Fobject"]["desc"]);
		$newObj->create();
		$newObj->createReference();

		$refGrpId = $newObj->getRefId();
		$GrpId = $newObj->getId();

		$newObj->putInTree($_GET["ref_id"]);
		unset($newObj);
		//rolefolder

		//create new rolefolder-object
		$newObj = new ilObject();
		$newObj->setType("rolf");
		$newObj->setTitle($_POST["Fobject"]["title"]);
		$newObj->setDescription($_POST["Fobject"]["desc"]);

		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($refGrpId);		//assign rolefolder to group
		$refRolf = $newObj->getRefId();
		unset($newObj);
		
		// create new role objects
		$newGrp = new ilObjGroup($GrpId,false);

		//0=public,1=private,2=closed
		$newGrp->setGroupStatus($_POST["group_status_select"]);

		//create standard group roles:member,admin,request(!),depending on group status(public,private,closed)
		$newGrp->createGroupRoles($refRolf); 		
		//creator becomes admin of group
		$newGrp->joinGroup($ilias->account->getId(),"admin");
		
		
		header("Location: adm_object.php?".$this->link_params);
		exit();

	}
	
} // END class.GroupObjectOut
?>
