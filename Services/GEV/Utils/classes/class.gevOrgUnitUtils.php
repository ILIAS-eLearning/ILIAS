<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for AdvancedMetadata of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author	Fabian Kochem <fabian.kochem@concepts-and-training.de>
* @version	$Id$
*/

require_once("Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php");
require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");

class gevOrgUnitUtils {
	static $instances = array();
	
	protected function __construct($a_orgu_id) {
		$this->orgu_id = $a_orgu_id;
		$this->gev_set = gevSettings::getInstance();
		$this->amd = gevAMDUtils::getInstance();
		
		$this->orgu_instance = null;
	}
	
	public static function getInstance($a_orgu_id) {
		if (array_key_exists($a_orgu_id, self::$instances)) {
			return self::$instances[$a_orgu_id];
		}
		
		self::$instances[$a_orgu_id] = new gevOrgUnitUtils($a_orgu_id);
		return self::$instance[$a_orgu_id];
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

	public function getOrgUnitInstance() {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		
		if ($this->orgu_instance === null) {
			$this->orgu_instance = new ilObjOrgUnit($this->orgu_id);
		}
		
		return $this->orgu_instance;
	}

	public function getType() {
		$obj = $this->getOrgUnitInstance();
		$type_id = $obj->getOrgUnitTypeId();
		
		foreach (gevSettings::ALL_ORG_TYPES as $org_type) {
			if ($this->gev_set->get($org_type) == $type_id) {
				return $org_type;
			}
		}
		
		return null;
	}
	
	public function getStreet() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_STREET);
	}
	
	public function getHouseNumber() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_HOUSE_NUMBER);
	}
	
	public function getZipcode() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_ZIPCODE);
	}
	
	public function getCity() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CITY);
	}
	
	public function getContactName() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_NAME);
	}
	
	public function getContactPhone() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_PHONE);
	}
	
	public function getContactFax() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_FAX);
	}
	
	public function getContactEmail() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_CONTACT_EMAIL);
	}
	
	public function getHomepage() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_HOMEPAGE);
	}
	
	public function getLocation() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_LOCATION);
	}
	
	public function getCostsPerAccomodation() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_ACCOM);
	}
	
	public function getCostsPerBreakfast() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_BREAKFAST);
	}
	
	public function getCostsPerLunch() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_LUNCH);
	}
	
	public function getCostsPerCoffee() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_COFFEE);
	}
	
	public function getCostsPerDinner() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_PER_DINNER);
	}
	
	public function getCostsPerDailyCatering() {
		return $this->amd->getField($this->orgu_id, gevSettings::ORG_AMD_COSTS_FOOD);
	}
}

?>