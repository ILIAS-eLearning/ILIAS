<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for NAs (Nebenberufsagenten) of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnits.php");

class gevNAUtils {
	static $instance;

	protected function __construct() {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		
		$this->gev_settings = gevSettings::getInstance();

		$this->pou = ilPersonalOrgUnits::getInstance(
			$this->gev_settings->getNAPOUBaseUnitId(),
			$this->gev_settings->getNAPOUTemplateUnitId()
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
	public function assignAdviser($a_user_id, $a_adviser_id) {
		$adv = $this->getAdviserOf($a_user_id);
		if ($adv !== null) {
			throw new Exception( "gevNAUtils::assignAdviser: User ".$a_user_id
								." is already assigned to adviser ".$adv);
		}
		$this->pou->assignEmployee($a_adviser_id, $a_user_id);
	}
	
	/**
	 * Entfernt die Zuweisung zum Betreuer.
	 *
	 * @param integer $a_user_id
	 */
	public function deassignAdviser($a_user_id) {
		$adv = $this->getAdviserOf($a_user_id);
		if ($adv !== null) {
			return;
		}
		$this->pou->deassignEmployee($adv, $a_user_id);
	}
	
	/**
	 * Gibt null zurück, wenn der Benutzer keinen Betreuer hat. 
	 * Gibt ansonsten die ILIAS-Id des Betreuers zurück.
	 *
	 * @param integer $a_user_id
	 * @return null|integer
	 */
	public function getAdviserOf($a_user_id) {
		$advs = $this->pou->getSuperiorsOf($a_user_id);
		if (count($advs) > 0) {
			return $advs[0];
		}
		return null;
	}
	
	/**
	 * Gibt die ILIAS-Ids aller NAs des Betreuers in einer Liste zurück.
	 *
	 * @param integer $a_adviser_id
	 * @return array
	 */
	public function getNAsOf($a_adviser_id) {
		return $this->pou->getEmployeesOf();
	}
}

?>