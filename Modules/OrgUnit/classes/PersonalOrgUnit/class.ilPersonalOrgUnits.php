<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPersonalOrgUnits
 *
 * @author 	Richard Klees <rklees@concepts-and-training.de>
 * @author  Nils Haagen <nhaagen@concepts-and-training.de>
 */



require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnitsException.php");

class ilPersonalOrgUnits {
	
	static $instances = array();

	//obj_id of (parent) org-unit-folder 
	protected $base_id = null;
	protected $base_ref_id = null;
	//obj_id of org unit to use as template for new units
	protected $template_id = null;
	protected $template_ref_id = null;
	
	public $orgu_base = null;
	public $orgu_template = null;
	
	//user object of the superior
	public $user_obj = array();

	protected function __construct($a_obj_id_base, $a_obj_id_template) {
		$this->base_id = $a_obj_id_base;
		$this->template_id = $a_obj_id_template;
		$this->base_ref_id = $this->getRefId($a_obj_id_base);
		$this->template_ref_id = $this->getRefId($a_obj_id_template);
	}



	protected function getRefId($a_obj_id) {
		global $ilDB;
		$res = $ilDB->query("SELECT ref_id FROM object_reference WHERE obj_id = ".$ilDB->quote($a_obj_id));
		if ($ret = $ilDB->fetchAssoc($res)) {
			return $ret["ref_id"];
		}
		return null;
	}

	protected function getBaseOrgu(){
		if($this->orgu_base == null){
			$this->orgu_base = new ilObjOrgUnit($this->base_ref_id);
		}
		return $this->orgu_base;
	}

	protected function getTemplateOrgu(){
		if($this->orgu_template == null){
			$this->orgu_template = new ilObjOrgUnit($this->template_ref_id);
		}
		return $this->orgu_template;
	}

	protected function getUserObj($a_user_id){
		require_once("Services/User/classes/class.ilObjUser.php");
		if ($this->user_obj[$a_user_id] === null) {
			$this->user_obj[$a_user_id] = new ilObjUser($a_user_id);
		}
		return $this->user_obj[$a_user_id];
	}

	static protected function buildOrguTitleFromUser($a_user_obj){
		$title = $a_user_obj->getFullname() .' (' .$a_user_obj->getLogin() .')';
		return $title;
	}
	
	protected function buildOrguTitleFromUserId($a_user_obj_id){
		$user = $this->getUserObj($a_user_obj_id);
		return self::buildOrguTitleFromUser($user);
	}

	protected function getPersonalOrguBySuperiorId($a_superior_id){
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
		global $ilDB;
		$query = "SELECT orgunit_id FROM org_unit_personal"
			 	." WHERE usr_id=" .$ilDB->quote($a_superior_id, 'integer');
		$res = $ilDB->query($query);
		$base_children = ilObjOrgUnitTree::_getInstance()->getAllChildren($this->base_ref_id);
		while($rec = $ilDB->fetchAssoc($res)) {
			$ref_id = $this->getRefId($rec['orgunit_id']);
			// Only take org units into account that are below the base unit, since
			// user could have POUs under different base units. 
			if (!in_array($ref_id, $base_children)) {
				continue;
			}
			$orgu = new ilObjOrgUnit($ref_id);
			return $orgu;
		}
		return null;
	}
	
	protected function getClassName() {
		return "ilPersonalOrgUnits";
	}
	
	public function ilPersonalOrgUnitsError($a_fn, $a_msg){
		$msg = $this->getClassName()."::".$a_fn
				." -> ". $a_msg;
		throw new ilPersonalOrgUnitsException($msg);
	}

	protected function errorIfNull($a_orgu, $a_fn, $a_superior_id){
		if($a_orgu === null){
			$msg = "The PersonalOrgUnit for user $a_superior_id does not exist.";
			$this->ilPersonalOrgUnitsError($a_fn, $msg);
		}
	}	
	protected function errorIfNotNull($a_orgu, $a_fn, $a_superior_id){
		if($a_orgu !== null){
			$msg = "The PersonalOrgUnit for user $a_superior_id already exists.";
			$this->ilPersonalOrgUnitsError($a_fn, $msg);
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
	static public function getInstance($a_obj_id_base, $a_obj_id_template){

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
	* @return ilObjOrgUnit 
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
		$new_orgu->setOwner(6);
		$new_orgu->updateOwner();
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

		return $new_orgu;
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
		if ($orgu === null) {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			$orgu = $this->createOrgUnitFor($a_superior_id);
		}
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
		$orgu->deassignUserFromEmployeeRole($a_employee_id);
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
	 * Get the id of the superior of the org unit. Throws when orgu does not
	 * belong to this personal org units.
	 *
	 * @param integer $a_orgu_id
	 *
	 * @return integer
	 */
	public function getOwnerOfOrgUnit($a_orgu_id) {
		global $ilDB, $tree;
		
		$ref_ids = ilObject::_getAllReferences($a_orgu_id);

		if  (  ilObject::_lookupType($a_orgu_id) != "orgu"
			|| count($ref_ids) != 1
			|| !$tree->isGrandChild($this->base_ref_id, array_shift($ref_ids))
			) {
			$this->ilPersonalOrgUnitsError(
				"getOwnerOfOrgUnit",
				"The object with id $a_orgu_id does not belong to "
				 ."personal org unit tree starting at ref id "
				 .$this->base_ref_id.".");
		}
		
		$res = $ilDB->query("SELECT usr_id"
						   ."  FROM org_unit_personal"
						   ." WHERE orgunit_id = ".$this->db->quote($a_orgu_id, "integer")
						   );
		if ($rec = $ilDB->fetchAssoc($res)) {
			return $rec["usr_id"];
		}
		else {
			$this->ilPersonalOrgUnitsError(
				"getOwnerOfOrgUnit",
				"Could not find an owner of $a_orgu_id");
		}
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

		$this->purgeOrgUnitLookupOf($orgu->getid());
		$orgu->delete();
	} 

	/**
	* Remove the org-unit/superior entry in org_unit_personal
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
	public function updateOrgUnitTitleOf($a_superior, $supress_error=False){
		$orgu = $this->getPersonalOrguBySuperiorId($a_superior->getId());
		if(! $supress_error){
			$this->errorIfNull($orgu, 'updateOrgUnitTitleOf', $a_superior_id);
		}
		if($orgu){
			$title = $this->buildOrguTitleFromUser($a_superior);
			$orgu->setTitle($title);
			$orgu->update();
		}
	}
	
	/**
	 * Update all personal org units titles that are owned by the superior.
	 */
	static public function updateAllOrgUnitTitlesOf($a_superior) {
		global $ilDB;
		
		$res = $ilDB->query("SELECT orgunit_id FROM org_unit_personal"
						   ." WHERE usr_id = ".$ilDB->quote($a_superior->getId(), "integer")
						   );
		
		while ($rec = $ilDB->fetchAssoc($res)) {
			$orgu = new ilObjOrgUnit($rec["orgunit_id"], false);
			$title = self::buildOrguTitleFromUser($a_superior);
			$orgu->setTitle($title);
			$orgu->update();
		}
	}

}

?>
