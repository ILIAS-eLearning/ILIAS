<?php
/**
* Class ilObjGroupGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjGroupGUI.php,v 1.2 2003/03/28 10:30:36 shofmann Exp $
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
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
	/**
	* save Object
	* @access public
	*/
	function saveObject()
	{
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
		
		//TODO TEMPLATE ERWEITERN->STATUS_RADIO_BOX		

		// create new role objects
		$newGrp = new ilObjGroup($GrpId,false);

		//TODO: extend template with radio boxes
		//0=public,1=private,2=closed
		$newGrp->setGroupStatus(1);

		//create standard group roles:member,admin,request(!),depending on group status(public,private,closed)
		$newGrp->createGroupRoles($refRolf); 		
		//creator becomes admin of group
		$newGrp->joinGroup($ilias->account->getId(),"admin");

		header("Location: adm_object.php?".$this->link_params);
		exit();

	}
	
} // END class.GroupObjectOut
?>
