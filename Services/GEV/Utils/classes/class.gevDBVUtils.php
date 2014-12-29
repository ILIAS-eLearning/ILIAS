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
require_once("Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnits.php");

class gevDBVUtils {
	static $instance;

	protected function __construct() {
		global $ilDB, $ilias, $ilLog;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		
		$this->gev_settings = gevSettings::getInstance();

		$this->pou = ilPersonalOrgUnits::getInstance(
			$this->gev_settings->getDBVPOUBaseUnitId(),
			$this->gev_settings->getDBVPOUTemplateUnitId()
			);
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	

	/**
	* "Ein Makler meldet sich mit einer Stellennummer an. 
	* Dieser Stellennummer ist in den IV-Daten die ID einer Organisationseinheit 
	* zugeordnet. 
	* Über die ID der Organisationseinheit lassen sich in den IV-Daten die 
	* Stellennummern der zugeordneten DBVen erfragen. 
	* Über die Stellennummer lässt sich der ILIAS-Benutzer eines DBVs bestimmen. 
	* Ein Makler kann so mehreren DBVen zugeordnet sein.
	* Ist der Organisationseinheit eines Benutzers in den IV-Daten kein 
	* DBV zugeordnet oder kann zu keiner DBV-Stellennummer eines Maklers ein 
	* ILIAS-Benutzer gefunden werden, sollen der Makler dem sogenannten 
	* C-Makler-Pool zugeordnet werden.""
	*
	* Macht den Benutzer zum Mitarbeiter in den Organisationseinheiten 
	* der DBVen, die über die oben genannte Tabelle der 
	* Generali-Org-Einheit $a_gev_org_unit_id zugewiesen sind. 
	* Ist der Generali-Org-Einheit kein DBV zugeordnet oder 
	* hat keiner der zugeordneten DBVen einen ILIAS-Benutzer, 
	* soll der Makler der Organisationseinheit C-Makler-Pool zugeordnet werden.
	*
	* Dient zum Zuordnen der User bei der Registirerung!
	*
	* @param integer $a_gev_org_unit_id
	* @param integer $a_user_id
	*
	*/
	public function assignUserToDBVsByShadowDB($a_user_id){
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		
		global $ilClientIniFile, $ilDB;
		
		$utils = gevUserUtils::getInstance($a_user_id);
		
		$job_number = $utils->getJobNumber();
		
		if (!$job_number) {
			return;
		}
		
		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass);
		
		if (!$mysql) {
			throw new Exception("gevDBVUtils::assignUserToDBVsByShadowDB: Can't connect to shadow db.");
		}
		
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		//get DBVs
		$sql= "SELECT "
			." ou.dbaf,"
			." ou.dbbav,"
			." ou.dbvg,"
			." ou.dbatv"
			." FROM ivimport_orgunit ou "
			." INNER JOIN ivimport_stelle stelle "
			."         ON ou.id = stelle.sql_org_unit_id "
			."        AND stelle.stellennummer = '".$job_number."' "
			;
		
		$result = mysql_query($sql, $mysql);
		$record = mysql_fetch_assoc($result);

		if (!$record) {
			return;
		}

		$job_number_field_id = $this->gev_settings->getUDFFieldId(gevSettings::USR_UDF_JOB_NUMMER);

		$assigned = false;
		foreach($record as $key => $dbv_job_number) {
			if (!$dbv_job_number) {
				continue;
			}
			$res = $this->db->query( "SELECT usr_id FROM udf_text "
									."WHERE field_id = ".$this->db->quote($job_number_field_id, "integer")
									."  AND value = ".$this->db->quote($dbv_job_number, "text")
									);
			// There might be many people having the job number, i'll take
			// rather all of them then dying or only the first one. 
			while($rec = $this->db->fetchAssoc($res)) {
				// NAs can't be DBVs (#863)
				$uu = gevUserUtils::getInstance($rec["usr_id"]);
				if ($uu->isNA()) {
					continue;
				}
				$this->pou->assignEmployee($rec["usr_id"], $a_user_id);
				$assigned = true;
			}
		}

		if (!$assigned) {
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			$cpool_id = $this->gev_settings->getCPoolUnitId();
			if (!$cpool_id) {
				throw new Exception("gevDBVUtils::assignUserToDBVsByShadowDB: No CPool-Org-Unit set.");
			}
			gevOrgUnitUtils::getInstance($cpool_id)->assignUser($a_user_id, "Mitarbeiter");
		}
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