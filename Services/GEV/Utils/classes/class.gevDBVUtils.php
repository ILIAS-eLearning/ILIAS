<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author	Nils Haagen <nils.haagen@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Modules/OrgUnit/PersonalOrgUnit/class.ilPersonalOrgUnits.php");

class gevDBVUtils {
	static $instance;

	protected function __construct() {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;

		$this->pou = ilPersonalOrgUnits::getInstance(
			gevSettings::PERSONAL_ORGUNITS_MAPPING['base'],
			gevSettings::PERSONAL_ORGUNITS_MAPPING['templates']				
			);
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	

	/**
	* Macht den Benutzer zum Mitarbeiter in den Organisationseinheiten 
	* der DBVen, die über die oben genannte Tabelle der 
	* Generali-Org-Einheit $a_gev_org_unit_id zugewiesen sind. 
	* Ist der Generali-Org-Einheit kein DBV zugeordnet oder 
	* hat keiner der zugeordneten DBVen einen ILIAS-Benutzer, 
	* soll der Makler der Organisationseinheit C-Makler-Pool zugeordnet werden.
	*
	*
	* @param integer $a_gev_org_unit_id
	* @param integer $a_user_id
	*
	*/
	public function assignDBVsOfOrgUnit($a_gev_org_unit_id, $a_user_id){
		die('NOT IMPLEMENTED gevDBVUtils::assignDBVsOfOrgUnit');
	}



	/**
	* Entfernt alle DBV-Zuweisungen für den Benutzer.
	*
	* @param integer $a_user_id
	*
	*/
	public function deassignDBVsOf($a_user_id){
		foreach($this->pou->getSuperiorsOf($a_user_id) as $superior_id){
			$this->pou->deassignEmployee($superior_id, $a_user_id);
		}
	} 

	/**
	* Gibt eine Liste aller DBVen zurück, 
	* denen der Benutzer zugeordnet ist.
	*
	* @param integer $a_user_id
	*
	* @return array 
	*/
	public function getDBVsOf($a_user_id){
 		return $this->pou->getSuperiorsOf($a_user_id);
	} 

	
	/**
	* Gibt eine Liste aller dem DBV zugewiesenen Makler zurück.
	*
	* @param integer $a_dbv_id
	*
	* @return array 
	*/
	public function getAgentsOf($a_dbv_id){
		return $this->pou->getEmployeesOf($a_dbv_id);
	} 



}

?>