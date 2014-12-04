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
		die('NOT IMPLEMENTED gevDBVUtils::assignUserToDBVsByShadowDB');

/*
		//from shadow db:
		//get user:
		sql_stelle_id FROM ivimport_adp WHERE ilias_id = $a_user_id
		
		//get his org_unit
		sql_org_unit_id FROM ivimport_stelle WHERE id = __above__sql_stelle_id

		//get the dbvs
	 	sql_dbaf_id,
	 	sql_dbbav_id,
	 	sql_dbvg_id,
	 	sql_dbatv_id FROM ivimport_orgunit WHERE id = __above__sql_org_unit_id

	 	
	 	for(sql_dbaf_id,
		 	sql_dbbav_id,
		 	sql_dbvg_id,
		 	sql_dbatv_id) {
	 			//get adb via stelle	
		 		sql_adp_id FROM ivimport_stelle WHERE id = __above__(sql_dbvg_id)
			 	//get superior-user:
			 	ilias_id FROM ivimport_adp WHERE id = __above__sql_adp_id
	 			
			 	//back to internal:
	 			//get orgunit of sup. and assign user.
	 	}
*/

		global $ilClientIniFile;
	 	$dbhost = $ilClientIniFile->readVariable('shadowdb', 'host');
		$dbuser = $ilClientIniFile->readVariable('shadowdb', 'user');
		$dbpass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$dbname = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$shdowDB = $mysql;
 	
	 	//get DBVs
	 	$sql= "SELECT "
	 		." sql_dbaf_id,"
	 		." sql_dbbav_id,"
	 		." sql_dbvg_id,"
	 		." sql_dbatv_id"

	 	." FROM ivimport_adp"

 		." INNER JOIN ivimport_stelle ON"
		." 	ivimport_adp.sql_stelle_id=ivimport_stelle.id"

		." INNER JOIN ivimport_orgunit ON"
		." 	ivimport_stelle.sql_org_unit_id=ivimport_orgunit.id"

		." WHERE ivimport_adp.ilias_id=" .$a_user_id;

		$result = mysql_query($sql, $shdowDB);
		$record = mysql_fetch_assoc($result);


		//get ilUsers for DBVs
		$sql = "SELECT ilias_id FROM ivimport_adp"
			." INNER JOIN ivimport_stelle ON"
			." ivimport_adp.id = ivimport_stelle.sql_adp_id"
			." WHERE ivimport_stelle.id IN "
			."(" 
			.implode(',', array_values($record));
			.")"

		$result = mysql_query($sql, $shdowDB);
		while($record = mysql_fetch_assoc($result)) {
			//assign user
			$superior_id = $record['ilias_id'];
			$this->pou->assignEmployee($superior_id, $a_user_id);
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