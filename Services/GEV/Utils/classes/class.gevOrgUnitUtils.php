<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for OrgUnits of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author	Fabian Kochem <fabian.kochem@concepts-and-training.de>
* @version	$Id$
*/

require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

class gevOrgUnitUtils {
	static $instances = array();
	static $venue_names = null;
	static $provider_names = null;

	protected function __construct($a_orgu_id) {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		$this->orgu_id = $a_orgu_id;
		$this->ref_id = null;
		$this->gev_set = gevSettings::getInstance();
		$this->amd = gevAMDUtils::getInstance();
		
		$this->local_roles = null;
		$this->flipped_local_roles = null;
		$this->role_folder = null;
		$this->orgu_instance = null;
		$this->orgu_type = false;
	}
	
	public static function getInstance($a_orgu_id) {
		if (array_key_exists($a_orgu_id, self::$instances)) {
			return self::$instances[$a_orgu_id];
		}
		
		self::$instances[$a_orgu_id] = new gevOrgUnitUtils($a_orgu_id);
		return self::$instances[$a_orgu_id];
	}
	
	static public function createOrgType($a_gev_setting, $a_title, $a_desc) {
		require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
		
		$type = new ilOrgUnitType();
		$type->setTitle($a_title, "de");
		$type->setDescription($a_title, "de");
		$type->setDefaultLang("de");
		$type->create();
		
		$id = $type->getId();
		gevSettings::getInstance()->set($a_gev_setting, $id);
	}
	
	static public function assignAMDRecordsToOrgUnitType($a_gev_setting, $a_amd_records) {
		$id = gevSettings::getInstance()->get($a_gev_setting);
		$type = new ilOrgUnitType($id);

		foreach ($a_amd_records as $amd_id) {
			$type->assignAdvancedMDRecord($amd_id);
		}
		$type->update();
	}
	
	static public function getVenueNames() {
		if (gevOrgUnitUtils::$venue_names !== null) {
			return gevOrgUnitUtils::$venue_names;
		}
		
		require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
		$id = gevSettings::getInstance()->get(gevSettings::ORG_TYPE_VENUE);

		$type = ilOrgUnitType::getInstance($id);
		$ou_ids = $type->getOrgUnitIDs(false);
		$ou_info = gevAMDUtils::getInstance()->getTable($ou_ids, array(gevSettings::ORG_AMD_CITY => "city"));

		gevOrgUnitUtils::$venue_names = array();
		foreach ($ou_info as $values) {
			gevOrgUnitUtils::$venue_names[$values["obj_id"]] = $values["title"].", ".$values["city"];
		}
		
		return gevOrgUnitUtils::$venue_names;
	}
	
	static public function getProviderNames() {
		if (gevOrgUnitUtils::$provider_names !== null) {
			return gevOrgUnitUtils::$provider_names;
		}
		
		require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
		$id = gevSettings::getInstance()->get(gevSettings::ORG_TYPE_PROVIDER);

		$type = ilOrgUnitType::getInstance($id);
		$ou_ids = $type->getOrgUnitIDs(false);
		$ou_info = gevAMDUtils::getInstance()->getTable($ou_ids, array(gevSettings::ORG_AMD_CITY => "city"));

		gevOrgUnitUtils::$provider_names = array();
		foreach ($ou_info as $values) {
			gevOrgUnitUtils::$provider_names[$values["obj_id"]] = $values["title"].", ".$values["city"];
		}
		
		return gevOrgUnitUtils::$provider_names;
	}
	
	public function getOrgUnitInstance() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		
		if ($this->orgu_instance === null) {
			try {
				$this->orgu_instance = new ilObjOrgUnit($this->orgu_id, false);
			}
			catch (Exception $e) {
				$this->orgu_instance = null;
			}
		}
		
		return $this->orgu_instance;
	}
	
	public function getRefId() {
		if ($this->ref_id === null) {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			$this->ref_id = gevObjectUtils::getRefId($this->orgu_id);
		}
		
		return $this->ref_id;
	}
	
	public function getTitle() {
		$inst = $this->getOrgUnitInstance();
		if ($inst === null) {
			return "";
		}
		return $inst->getTitle();
	}
	
	public function getLongTitle() {
		$title = $this->getTitle();
		if ($title) {
			return $title.", ".$this->getCity();
		}
		return "";
	}

	public function getType() {
		// this might be used a lot during setting of properties
		if ($this->orgu_type !== false) {
			return $this->orgu_type;
		}
		
		$obj = $this->getOrgUnitInstance();
		$type_id = $obj->getOrgUnitTypeId();
		$this->orgu_type = null;
		
		foreach (gevSettings::$all_org_types as $org_type) {
			if ($this->gev_set->get($org_type) == $type_id) {
				$this->orgu_type = $org_type;
			}
		}
		
		return $this->orgu_type;
	}
	
	public function setType($a_type) {
		$this->checkTypeValid("setType", $a_type);
		
		$type_id = $this->gev_set->get($a_type);
		$this->getOrgUnitInstance()->setOrgUnitTypeId($type_id);
		$this->getOrgUnitInstance()->update();
		$this->orgu_type = $a_type;
	}
	
	public function checkTypeValid($a_caller, $a_type = null) {
		if ($a_type === null) {
			$a_type = $this->getType();
		}
		
		if ( ! in_array($a_type, gevSettings::$all_org_types) ) {
			throw new Exception("gevOrgUnitUtils::".$a_caller.": unknown type '".$a_type."'");
		}
	}
	
	public function checkIsVenue($a_caller) {
		if ($this->getType() !== $this->gev_set->get(gevSettings::ORG_TYPE_VENUE)) {
			throw new Exception("gevOrgUnitUtils::".$a_caller.": orgunit '".$this->orgu_id."' is no venue.");
		}
	}
	
	public function getStreet() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_STREET);
	}
	
	public function setStreet($a_street) {
		$this->checkTypeValid("setStreet");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_STREET, $a_street);
	}
	
	public function getHouseNumber() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_HOUSE_NUMBER);
	}
	
	public function setHouseNumber($a_house_number) {
		$this->checkTypeValid("setHouseNumber");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_HOUSE_NUMBER, $a_house_number);
	}
	
	public function getZipcode() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_ZIPCODE);
	}
	
	public function setZipcode($a_zipcode) {
		$this->checkTypeValid("setZipcode");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_ZIPCODE, $a_zipcode);
	}
	
	public function getCity() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CITY);
	}
	
	public function setCity($a_city) {
		$this->checkTypeValid("setCity");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CITY, $a_city);
	}
	
	public function getContactName() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_NAME);
	}
	
	public function setContactName($a_name) {
		$this->checkTypeValid("setContactName");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_NAME, $a_name);
	}
	
	public function getContactPhone() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_PHONE);
	}
	
	public function setContactPhone($a_phone) {
		$this->checkTypeValid("setContactPhone");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_PHONE, $a_phone);
	}
	
	public function getContactFax() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_FAX);
	}
	
	public function setContactFax($a_fax) {
		$this->checkTypeValid("setContactFax");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_FAX, $a_fax);
	}
	
	public function getContactEmail() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_EMAIL);
	}
	
	public function setContactEmail($a_email) {
		$this->checkTypeValid("setContactEmail");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_EMAIL, $a_email);
	}
	
	public function getHomepage() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_HOMEPAGE);
	}
	
	public function setHomepage($a_homepage) {
		$this->checkTypeValid("setHomepage");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_HOMEPAGE, $a_homepage);
	}
	
	public function getLocation() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_LOCATION);
	}
	
	public function setLocation($a_loc) {
		$this->checkIsVenue("setLocation");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_LOCATION, $a_loc);
	}
	
	public function getCostsPerAccomodation() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_ACCOM);
	}
	
	public function setCostsPerAccomodation($a_costs) {
		$this->checkIsVenue("setCostsPerAccomodation");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_ACCOM, $a_costs);
	}
	
	public function getCostsPerBreakfast() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_BREAKFAST);
	}
	
	public function setCostsPerBreakfast($a_costs) {
		$this->checkIsVenue("setCostsPerBreakfast");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_BREAKFAST, $a_costs);
	}
	
	public function getCostsPerLunch() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_LUNCH);
	}
	
	public function setCostsPerLunch($a_costs) {
		$this->checkIsVenue("setCostsPerLunch");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_LUNCH, $a_costs);
	}
	
	public function getCostsPerCoffee() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_COFFEE);
	}
	
	public function setCostsPerCoffee($a_costs) {
		$this->checkIsVenue("setCostsPerCoffee");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_COFFEE, $a_costs);
	}
	
	public function getCostsPerDinner() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_DINNER);
	}
	
	public function setCostsPerDinner($a_costs) {
		$this->checkIsVenue("setCostsPerDinner");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_DINNER, $a_costs);
	}
	
	public function getCostsPerDailyCatering() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_FOOD);
	}
	
	public function setCostsPerDailyCatering($a_costs) {
		$this->checkIsVenue("setCostsPerDailyCatering");
		$this->amd->setField($this->orgu_id, gevSettings::ORG_AMD_COSTS_FOOD, $a_costs);
	}

	// Helpers and Caching for role related stuff
	
	public function getRoleFolder() {
		if ($this->role_folder === null) {
			$rolf_data = gevRoleUtils::getRbacReview()->getRoleFolderOfObject($this->getRefId());
			$this->role_folder  = $this->ilias->obj_factory->getInstanceByRefId($rolf_data["ref_id"]);
		}
		
		return $this->role_folder;
	}
	
	public function getLocalRoles() {
		if ($this->local_roles === null) {
			$review = gevRoleUtils::getRbacReview();
			$role_ids = $review->getLocalRoles($this->getRefId());
			
			$res = $this->db->query("SELECT obj_id, title FROM object_data ".
									" WHERE ".$this->db->in("obj_id", $role_ids, false, "integer")
									);
			
			$this->local_roles = array();
			while($rec = $this->db->fetchAssoc($res)) {
				$this->local_roles[$rec["obj_id"]] = $rec["title"];
			}
		}
		
		return $this->local_roles;
	}
	
	public function getFlippedLocalRoles() {
		if ($this->flipped_local_roles === null) {
			$this->flipped_local_roles = array_flip($this->getLocalRoles());
		}
		
		return $this->flipped_local_roles;
	}

	// assignment of users to the org-unit
	
	public function assignUser($a_user_id, $a_role_title) {
		if ($a_role_title == "Mitarbeiter") {
			$role_title = "il_orgu_employee_".$this->getRefId();
		}
		else if ($a_role_title == "Vorgesetzter") {
			$role_title = "il_orgu_superior_".$this->getRefId();	
		}
		else {
			$role_title = "Org-".$a_role_title;
		}

		$roles = $this->getFlippedLocalRoles();
		
		if (!array_key_exists($role_title, $roles)) {
			$this->log->write("gevOrgUnitUtils::assignUser: Could not find role with name ".$role_title.
							  " in Org-Unit with ref_id ".$this->getRefId());
			return;
		}
		
		gevRoleUtils::getRbacAdmin()->assignUser($roles[$role_title], $a_user_id);
	}
	
	// assignment and deassignment of standard org unit roles for the default org
	// units of the Generali.
	
	// by convention, the roles for org-units start with "Org-"
	static public function getRoleTemplatesForDefaultOrgUnits() {
		global $ilDB;
		
		$res = $ilDB->query("SELECT obj_id, title FROM object_data ".
							" WHERE title LIKE 'Org-%' ".
							"   AND type = 'rolt'"
							);
		$ret = array();
		while ($rec = $ilDB->fetchAssoc($res)) {
			$ret[$rec["obj_id"]] = $rec["title"];
		}
		
		return $ret;
	}
	
	public function hasRolesForDefaultOrgUnits() {
		$cur = $this->getLocalRoles();
		$to = $this->getRoleTemplatesForDefaultOrgUnits();

		foreach ($to as $id => $title) {
			if (!in_array($title, $cur)) {
				return false;
			}
		}
		return true;
	}
	
	public function addRolesForDefaultOrgUnits() {
		$review = gevRoleUtils::getRbacReview();
		$admin = gevRoleUtils::getRbacAdmin();
		$ref_id = $this->getRefId();
		$folder  = $this->getRoleFolder();
		
		$to = $this->getRoleTemplatesForDefaultOrgUnits();
		
		foreach ($to as $id => $title) {
			$role = $folder->createRole($title, "");
			$admin->copyRoleTemplatePermissions($id, ROLE_FOLDER_ID, $folder->getRefId(), $role->getId());
			$ops = $review->getOperationsOfRole($role->getId(), "orgu", $folder->getRefId());
			$admin->grantPermission($role->getId(), $ops, $ref_id);
		}
	}
	
	public function removeRolesForDefaultOrgUnits() {
		$admin = gevRoleUtils::getRbacAdmin();
		
		$cur = array_flip($this->getLocalRoles());
		$to = $this->getRoleTemplatesForDefaultOrgUnits();
		
		foreach ($to as $id => $title) {
			if (!array_key_exists($title, $cur)) {
				continue;
			}
			
			$id = $cur[$title];
			$admin->deassignUsers($id);
			$admin->deleteLocalRole($id);
		}
	}
}



class gevOrgUnitCache {
	protected $root_id;
	protected $cache = array();
	private $tree;

	public function __construct($root_id) {
		$this->root_id = $root_id;
		$this->tree = ilObjOrgUnitTree::_getInstance();
	}

	public function index() {
		$children = $this->tree->getAllChildren($this->root_id);
		foreach ($children as $child_id) {
			$child_obj = new ilObjOrgUnit($child_id);
			$import_id = $child_obj->getImportId();
			$this->addToCache($child_id, $import_id);
		}
	}

	public function addToCache($ref_id, $import_id) {
		if ($import_id === null) {
			$import_id = 'root';
		}

		if ($this->isImportIdInCache($import_id)) {
			die('Duplicate Import ID: ' . $import_id);
		}

		$this->cache[$import_id] = $ref_id;
	}

	public function isImportIdInCache($import_id) {
		return array_key_exists($import_id, $this->cache);
	}

	public function getRefIdByImportId($import_id) {
		return $this->cache[$import_id];
	}
}

?>