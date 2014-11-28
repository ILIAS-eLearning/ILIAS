<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPersonalOrgUnits
 *
 * @author 	Richard Klees <rklees@concepts-and-training.de>
 * @author  Nils Haagen <nhaagen@concepts-and-training.de>
 */



require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");

class ilPersonalOrgUnits {
	
	static $instances = array();

	//obj_id of (parent) org-unit-folder 
	private $base_id = null;
	private $base_ref_id = null;
	//obj_id of org unit to use as template for new units
	private $template_id = null;
	private $template_ref_id = null;
	
	public $orgu_base = null;
	public $orgu_template = null;

	//user object of the superior
	public $user_obj = null;

	function __construct($a_obj_id_base, $a_obj_id_template) {
		$this->base_id = $a_obj_id_base;
		$this->template_id = $a_obj_id_template;
		$this->base_ref_id = $this->getRefId($a_obj_id_base);
		$this->template_ref_id = $this->getRefId($a_obj_id_template);
	}



	private function getRefId($a_obj_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT ref_id FROM object_reference WHERE obj_id = ".$ilDB->quote($a_obj_id));
		if ($ret = $ilDB->fetchAssoc($res)) {
			return $ret["ref_id"];
		}
		return null;
	}

	private function getBaseOrgu(){
		if($this->orgu_base == null){
			$this->orgu_base = new ilObjOrgUnit($this->base_ref_id);
		}
		return $this->orgu_base;
	}

	private function getTemplateOrgu(){
		if($this->orgu_template == null){
			$this->orgu_template = new ilObjOrgUnit($this->template_ref_id);
		}
		return $this->orgu_template;
	}

	private function getUserObj($a_user_id){
		require_once("Services/User/classes/class.ilObjUser.php");
		if ($this->user_obj === null) {
			$this->user_obj = new ilObjUser($a_user_id);
		}
		return $this->user_obj;
	}

	private function buildOrguTitleFromUser($a_user_obj){
		$title = $a_user_obj->getFullname() .' (' .$a_user_obj->getLogin() .')';
		return $title;
	}
	
	private function buildOrguTitleFromUserId($a_user_obj_id){
		$user = $this->getUserObj($a_user_obj_id);
		return $this->buildOrguTitleFromUser($user);
	}

	private function getPersonalOrguBySuperiorId($a_superior_id){
		global $ilDB;
		$query = "SELECT orgunit_id FROM org_unit_personal"
			 	." WHERE usr_id=" .$ilDB->quote($a_superior_id, 'integer');
		$res = $ilDB->query($query);
		if($ilDB->numRows($res) > 0){
			$rec = $ilDB->fetchAssoc($res);
			$ref_id = self::getRefId($rec['orgunit_id']);
			$orgu = new ilObjOrgUnit($ref_id);
			return $orgu;
		}
		return null;
	}
		
	public function ilPersonalOrgUnitsError($a_fn, $a_msg){
		global $ilErr;
		$msg = "ilPersonalOrgUnits::"
			.$a_fn
			." -> "
			. $a_msg;
		//$ilErr->raiseError($msg,$ilErr->WARNING);
		print $msg;	
		$ilErr->raiseError($msg,$ilErr->FATAL);
	}

	private function errorIfNull($a_orgu, $a_fn, $a_superior_id){
		print_r($orgu);
		if($a_orgu === null){
			$msg = "The PersonalOrgUnit for user $a_superior_id does not exist.";
			self::ilPersonalOrgUnitsError($a_fn, $msg);
		}
	}	
	private function errorIfNotNull($a_orgu, $a_fn, $a_superior_id){
		if($a_orgu !== null){
			$msg = "The PersonalOrgUnit for user $a_superior_id already exists.";
			self::ilPersonalOrgUnitsError($a_fn, $msg);
		}
	}



	/**
	* Instantiate the toolbox, 
	* where $a_obj_id identifies the node in the org-structure 
	* where the org-units for the superiors are put 
	* and $a_obj_id_template identifies an org-unit that should be used 
	* as a template for the org-unit of a superior.
	* @param integer $a_obj_id_base
	* @param integer $a_obj_id_template
	*/	
	//ilPersonalOrgUnits(integer $a_obj_id_base, integer $a_obj_id_template): 
	public function getInstance($a_obj_id_base, $a_obj_id_template){

		//verify, throw exception on failure

		$instance_id = (string)$a_obj_id_base .'##'. (string)$a_obj_id_template;
		if (array_key_exists($instance_id, self::$instances)) {
			return self::$instances[$instance_id];
		}

		self::$instances[$instance_id] = new ilPersonalOrgUnits($a_obj_id_base, $a_obj_id_template);
		return self::$instances[$instance_id];
	}


	/**
	* If there currently exists no org-unit for the superior 
	* create one under the node identified by a_obj_base 
	* from the constructor by copying the template org unit. 
	* Assign the superior as superior to the org-unit and name 
	* the org-unit as „firstname, lastname“ of the superior. 
	*
	* If there is an org-unit for the superior, raise an exception. 
	* Return the id of the new org-unit.
	*
	* @param integer $a_superior_id
	*
	* @return integer 
	*/
	public function createOrgUnitFor($a_superior_id){
		$this->errorIfNotNull(
			$this->getPersonalOrguBySuperiorId($a_superior_id),
			'createOrgUnitFor',
			$a_superior_id
		);

		$title = $this->buildOrguTitleFromUserId($a_superior_id); 

		$template = $this->getTemplateOrgu();
		$target_id = $this->base_ref_id;

		$new_orgu = $template->cloneObject($target_id);
		$new_orgu->setTitle($title);
		$new_orgu->update();
		//assign user as superior
		$new_orgu->assignUsersToSuperiorRole(array($a_superior_id));


		//insert into lookup-table
		global $ilDB;
		$query = "INSERT INTO org_unit_personal"
			   ." (orgunit_id, usr_id)"
			   ." VALUES ("
			   .$new_orgu->getId()
			   ."," 
			   .$ilDB->quote($a_superior_id, 'integer') 
			   .")";

		$ilDB->manipulate($query);

		return $new_orgu->getId();
	}


	/**
	* Put the employee in the org-unit of the superior. 
	* If there currently exists no org-unit fort the superior, 
	* use createOrgUnitFor to create one and then 
	* assign employee as employee to the org-unit.
	*
	* @param integer $a_superior_id
	* @param integer $a_employee_id
	*
	*/
	public function assignEmployee($a_superior_id, $a_employee_id){
		$orgu = $this->getPersonalOrguBySuperiorId($a_superior_id);
		$this->errorIfNull($orgu, 'assignEmployee', $a_superior_id);
		$orgu->assignUsersToEmployeeRole(array($a_employee_id));
	} 


	/**
	* Remove the employee from the org-unit of the superior. 
	* If there is no org-unit for the superior, raise an exception.
	*
	* @param integer $a_superior_id
	* @param integer $a_employee_id
	*
	*/
	public function deassignEmployee($a_superior_id, $a_employee_id){
		$orgu = $this->getPersonalOrguBySuperiorId($a_superior_id);
		$this->errorIfNull($orgu, 'deassignEmployee', $a_superior_id);
		$orgu->deassignUserFromEmployeeRole(array($a_employee_id));
	} 


	
	/**
	* Get the id of the org-unit of the superior. 
	* Return null if there currently exists no org-unit fort he superior.	
	*
	* @param integer $a_superior_id
	*
	* @return integer|null
	*/
	public function getOrgUnitIdOf($a_superior_id){
		$orgu = $this->getPersonalOrguBySuperiorId($a_superior_id);
		if($orgu !== null){
			return $orgu->getId();
		}
		return null;
	} 

	/**
	* Get the org-unit of the superior. 
	* Return null if there currently exists no org-unit fort the superior.	
	*
	* @param integer $a_superior_id
	*
	* @return obj orgu|null
	*/
	public function getOrgUnitOf($a_superior_id){
		return $this->getPersonalOrguBySuperiorId($a_superior_id);
	} 

	
	
	/**
	* Get the Ids of the employees in the org-unit of the superior. 
	* Raise an exception if there is no org-unit for the superior.
	*
	* @param integer $a_superior_id
	*
	* @return array
	*/
	public function getEmployeesOf($a_superior_id){
		require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$orgu = $this->getPersonalOrguBySuperiorId($a_superior_id);
		$this->errorIfNull($orgu, 'getEmployeesOf', $a_superior_id);

		$ref_id = $orgu->getRefId();
		$employees = ilObjOrgUnitTree::_getInstance()->getEmployees($ref_id, true);
		return $employees;
	} 


	
	/**
	* Get the ids of all superiors of the employee 
	* in the org-unit-substructure maintained by this object.
	*
	* @param integer $a_employee
	*
	* @return array
	*/
	public function getSuperiorsOf($a_employee_id){
		require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		$superiors = array_unique(ilObjOrgUnitTree::_getInstance()->getSuperiorsOfUser($a_employee_id, true));
		$ret = array();
		foreach ($superiors as $superior_id) {
			if($superior_id != $a_employee_id) {
				if($this->getPersonalOrguBySuperiorId($superior_id) !== null){
					$ret[] = $superior_id;
				}
			}
		}
		return $ret;
	} 

	
	/**
	* Remove the org-unit of the given superior. 
	* If there is no org-unit for the superior, raise an exception.
	*
	* @param integer $a_superior_id
	*
	*/
	public function purgeOrgUnitOf($a_superior_id){
		$orgu = $this->getPersonalOrguBySuperiorId($a_superior_id);
		$this->errorIfNull($orgu, 'purgeOrgUnitOf', $a_superior_id);

		self::purgeOrgUnitLookupOf($orgu->getid());
		$orgu->delete();
	} 

	/**
	* Remove the org-unit/superior entry in org_unit_personal_units
	*
	* @param integer $a_orgunit_id
	*
	*/
	public function purgeOrgUnitLookupOf($a_orgunit_id){
		global $ilDB;
		$query="DELETE FROM org_unit_personal WHERE orgunit_id=".$ilDB->quote($a_orgunit_id, 'integer');
		$ilDB->manipulate($query);
	}


	/**
	* Update the org-unit's title by the given superior's user data. 
	* If there is no org-unit for the superior, raise an exception.
	*
	* @param obj user $a_superior
	*
	*/
	public function updateOrgUnitTitleOf($a_superior){
		$orgu = self::getPersonalOrguBySuperiorId($a_superior->getId());
		self::errorIfNull($orgu, 'updateOrgUnitTitleOf', $a_superior_id);

		$title = self::buildOrguTitleFromUser($a_superior);

		$orgu->setTitle($title);
		$orgu->update();
	}


}

?>
