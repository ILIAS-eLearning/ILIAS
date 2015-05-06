<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for HAs (Hauptagenturen) of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnits.php");

class gevHAUtils {
	static $instance;

	protected function __construct() {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->log = &$ilLog;
		
		$this->gev_settings = gevSettings::getInstance();

		$this->pou = ilPersonalOrgUnits::getInstance(
			$this->gev_settings->getHAPOUBaseUnitId(),
			$this->gev_settings->getHAPOUTemplateUnitId()
			);
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 *  Wirft eine Exception, wenn dem Benutzer bereits ein Betreuer zugewiesen ist. 
	 * Macht den Benutzer zum Mitarbeiter in der Organisationseinheit des Betreuers.
	 *
	 * @param integer $a_user_id
	 * @param integer $a_adviser_id
	 */
	public function assignEmployee($a_ha_id, $a_employee_id) {
		$this->pou->assignEmployee($a_ha_id, $a_user_id);
	}
	
	/**
	 * Entfernt die Zuweisung zum Betreuer.
	 *
	 * @param integer $a_user_id
	 */
	public function deassignEmployee($a_ha_id, $a_employee_id) {
		$this->pou->deassignEmployee($adv, $a_user_id);
	}
	
	/**
	 * Hat der Benutzer bereits eine HA Einheit?
	 */
	public function hasHAUnit($a_ha_id) {
		return $this->pou->getOrgUnitIdOf($a_ha_id) !== null;
	}

	/**
	 * Erzeugt dem Benutzer eine neue HA-Einheit.
	 */
	public function createHAUnit($a_ha_id) {
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		$org_id = $this->pou->createOrgUnitFor($a_ha_id)->getId();
		gevOrgUnitUtils::getInstance($org_id)
			->grantPermissionsFor("superior", array("cat_administrate_users", "read_users"));
		
		return $org_id;
	}
}

?>