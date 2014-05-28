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

class gevOrgUnitUtils {
	protected function __construct() {
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
}

?>