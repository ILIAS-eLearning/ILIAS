<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * Date: 4/07/13
 * Time: 12:37 PM
 * To change this template use File | Settings | File Templates.
 */

require_once("./Modules/Category/classes/class.ilObjCategory.php");
require_once("./Modules/OrgUnit/classes/class.ilOrgUnitImporter.php");

class ilObjOrgUnit extends ilObjCategory {

	protected static $root_ref_id;
	protected static $root_id;

	protected $employee_role;
	protected $superior_role;

	public function __construct($a_id = 0,$a_call_by_reference = true){
		$this->type = "orgu";
		$this->ilContainer($a_id,$a_call_by_reference);
	}

	public static function getRootOrgRefId(){
		self::loadRootOrgRefIdAndId();
		return self::$root_ref_id;
	}

	public static function getRootOrgId(){
		self::loadRootOrgRefIdAndId();
		return self::$root_id;
	}

	private static function loadRootOrgRefIdAndId(){
		if(self::$root_ref_id === Null || self::$root_id === null){
			global $ilDB;
			$q = "SELECT o.obj_id, r.ref_id FROM object_data o
			INNER JOIN object_reference r ON r.obj_id = o.obj_id
			WHERE title = ".$ilDB->quote('__OrgUnitAdministration', 'text')."";
			$set = $ilDB->query($q);
			$res = $ilDB->fetchAssoc($set);
			self::$root_id = $res["obj_id"];
			self::$root_ref_id= $res["ref_id"];
		}
	}

	private function loadRoles(){
		global $ilDB, $ilLog;
		if(!$this->employee_role || !$this->superior_role){
				$this->doLoadRoles();
			}

			if(!$this->employee_role || !$this->superior_role){
				$this->initDefaultRoles();
				$this->doLoadRoles();
				if(!$this->employee_role || !$this->superior_role)
					throw new Exception("The standard roles the orgu object with id: ".$this->getId()." aren't initialized or have been deleted, newly creating them didn't work!");
				else
					$ilLog->write("[".__FILE__.":".__LINE__."] The standard roles for the orgu obj with id: ".$this->getId()." were newly created as they couldnt be found.");
			}
	}

	private function doLoadRoles(){
		global $ilDB;
		$q = "SELECT obj_id, title FROM object_data WHERE title LIKE 'il_orgu_employee_".$ilDB->quote($this->getRefId(),"integer")."' OR title LIKE 'il_orgu_superior_".$ilDB->quote($this->getRefId(),"integer")."'";
		$set = $ilDB->query($q);
		while($res = $ilDB->fetchAssoc($set)){
			if($res["title"] == "il_orgu_employee_".$this->getRefId())
				$this->employee_role = $res["obj_id"];
			elseif($res["title"] == "il_orgu_superior_".$this->getRefId())
				$this->superior_role = $res["obj_id"];
		}
	}

	public function assignUsersToEmployeeRole($user_ids){
		global $rbacadmin;
		foreach($user_ids as $user_id)
			$rbacadmin->assignUser($this->getEmployeeRole(), $user_id);
	}

	public function assignUsersToSuperiorRole($user_ids){
		global $rbacadmin;
		foreach($user_ids as $user_id)
			$rbacadmin->assignUser($this->getSuperiorRole(), $user_id);
	}

	public function deassignUserFromEmployeeRole($user_id){
		global $rbacadmin;
		$rbacadmin->deassignUser($this->getEmployeeRole(), $user_id);
	}

	public function deassignUserFromSuperiorRole($user_id){
		global $rbacadmin;
		$rbacadmin->deassignUser($this->getSuperiorRole(), $user_id);
	}

	/**
	 * @param int $employee_role
	 */
	public function setEmployeeRole($employee_role)
	{
		$this->employee_role = $employee_role;
	}

	public static function _exists($a_id, $a_reference = false){
		return parent::_exists($a_id, $a_reference, "orgu");
	}

	/**
	 * @return int
	 */
	public function getEmployeeRole()
	{
		$this->loadRoles();
		return $this->employee_role;
	}

	/**
	 * @param int $superior_role
	 */
	public function setSuperiorRole($superior_role)
	{
		$this->superior_role = $superior_role;
	}

	/**
	 * @return int
	 */
	public function getSuperiorRole()
	{
		$this->loadRoles();
		return $this->superior_role;
	}

	public function initDefaultRoles(){
		global $rbacadmin,$rbacreview;

		$rolf_obj = $this->createRoleFolder();

		// CREATE Employee ROLE
		$role_obj = $rolf_obj->createRole("il_orgu_employee_".$this->getRefId(),"Emplyee of org unit obj_no.".$this->getId());
// = $
// EMPLOYEE DOES NOT YET NEED A ROLE TEMPLATE.
//		// SET PERMISSION TEMPLATE OF NEW LOCAL ADMIN ROLE
//		$query = "SELECT obj_id FROM object_data ".
//			" WHERE type='rolt' AND title='il_orgu_employee'";
//
//		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
//		$rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());
//
//		// SET OBJECT PERMISSIONS OF COURSE OBJECT
//		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"orgu",$rolf_obj->getRefId());
//		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// CREATE Superior ROLE
		$role_obj = $rolf_obj->createRole("il_orgu_superior_".$this->getRefId(),"Superior of org unit obj_no.".$this->getId());

		// SET PERMISSION TEMPLATE OF NEW LOCAL ADMIN ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_orgu_superior'";

		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"orgu",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());
	}
	public function getTitle(){
		if(parent::getTitle() != "__OrgUnitAdministration")
			return parent::getTitle();
		else
			return $this->lng->txt("obj_orgu");
	}

	/**
	 * @return array This catches if by some means there is no translation.
	 */
	public function getTranslations(){
		global $lng;
		$translations =  parent::getTranslations();
		if(!count($translations["Fobject"])){
			$this->addTranslation($this->getTitle(), "", $lng->getDefaultLanguage(), true);
			$translations["Fobject"][] = array("title"	=> $this->getTitle(),
				"desc"	=> "",
				"lang"	=> $lng->getDefaultLanguage());
		}
		return $translations;
	}
}