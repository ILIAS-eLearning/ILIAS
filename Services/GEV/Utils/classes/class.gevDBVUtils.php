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
require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
require_once("Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnits.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");

class gevDBVUtils {
	static $instance;

	protected function __construct() {
		global $ilDB, $ilias, $ilLog, $ilAppEventHandler;
		$this->db = &$ilDB;
		$this->ilias = &$ilias;
		$this->log = &$ilLog;
		$this->appEventHandler = &$ilAppEventHandler;
		$this->orgu_tree = ilObjOrgUnitTree::_getInstance();
		
		$this->gev_settings = gevSettings::getInstance();

		$this->pou = gevUVGOrgUnits::getInstance();
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
		$dbv_ids = $this->iv_getDBVsUserIdsOf($a_user_id);
		if (count($dbv_ids) > 0) {
			foreach ($dbv_ids as $dbv_id) {
				$this->pou->assignEmployee($dbv_id, $a_user_id);
			}
		}
		else {
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			$cpool_id = $this->gev_settings->getCPoolUnitId();
			if (!$cpool_id) {
				throw new Exception("gevDBVUtils::assignUserToDBVsByShadowDB: No CPool-Org-Unit set.");
			}
			gevOrgUnitUtils::getInstance($cpool_id)->assignUser($a_user_id, "Mitarbeiter");
			$this->appEventHandler->raise("Modules/OrgUnit", "afterUpdate", array("user_id" => $a_user_id));
		}
	}
	
	public function updateUsersDBVAssignmentsByShadowDB($a_user_id) {
		// remove existing assignments
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		$cpool_id = $this->gev_settings->getCPoolUnitId();
		if (!$cpool_id) {
			throw new Exception("gevDBVUtils::assignUserToDBVsByShadowDB: No CPool-Org-Unit set.");
		}
		gevOrgUnitUtils::getInstance($cpool_id)->deassignUser($a_user_id, "Mitarbeiter");
		
		$dbvs = $this->pou->getSuperiorsOf($a_user_id);
		foreach ($dbvs as $dbv) {
			$this->pou->deassignEmployee($dbv, $a_user_id);
		}
		
		// make new assignments
		$this->assignUserToDBVsByShadowDB($a_user_id);
	}
	/**
	* DBV_update refactoring to avoid reassignments into the same org unit. 
	* This creates a lot of pointless entries in hist_userorgu table and slows down reports. 
	*/
	public function updateUsersDBVAssignmentsByShadowDB_new($a_user_id) {
		$cpool_id = $this->gev_settings->getCPoolUnitId();
		$dbvs_init = $this->pou->getSuperiorsOf($a_user_id);
		$dbvs_final = $this->iv_getDBVsUserIdsOf($a_user_id);
		$dbvs_out = array_diff($dbvs_init, $dbvs_final);
		$dbvs_in = array_diff($dbvs_final, $dbvs_init);

		foreach ($dbvs_out as $dbv) {
			$this->pou->deassignEmployee($dbv, $a_user_id);
		}

		if(count($dbvs_final) == 0) {
			if (!$cpool_id) {
				throw new Exception("gevDBVUtils::assignUserToDBVsByShadowDB: No CPool-Org-Unit set.");
			}
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			gevOrgUnitUtils::getInstance($cpool_id)->assignUser($a_user_id, "Mitarbeiter");
		} else {
			if(count($dbv_init) == 0) { 
				gevOrgUnitUtils::getInstance($cpool_id)->deassignUser($a_user_id, "Mitarbeiter");
			}
			foreach ($dbvs_in as $dbv) {
				$this->pou->assignEmployee($dbv, $a_user_id);
			}
		}
		$this->appEventHandler->raise("Modules/OrgUnit", "afterUpdate", array("user_id" => $a_user_id));
	}

	/**
	 * Gibt die Benutzernummern der DBVs eines Benutzer mit Daten aus der IV
	 * zurück.
	 *
	 * @param integer $a_user_id
	 */
	public function iv_getDBVsUserIdsOf($a_user_id) {
		$job_number_field_id = $this->gev_settings->getUDFFieldId(gevSettings::USR_UDF_JOB_NUMMER);
		
		$job_numbers = $this->iv_getJobNumbersOfDBVsOf($a_user_id);
		$res = $this->db->query("SELECT usr_id"
							   ."  FROM udf_text"
							   ." WHERE field_id = ".$this->db->quote($job_number_field_id, "integer")
							   ."   AND ".$this->db->in("value", $job_numbers, false, "text")
							   );
		$result = array();
		while ($rec = $this->db->fetchAssoc($res)) {
				$result[] = $rec["usr_id"];
		}
		return $result;
	}

	/**
	 * Gibt die Stellennummern der DBVs eines Benutzer aus der IV zurück.
	 *
	 * @param integer $a_user_id
	 */
	public function iv_getJobNumbersOfDBVsOf($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		
		global $ilClientIniFile;
		
		$utils = gevUserUtils::getInstance($a_user_id);
		
		$job_number = $utils->getJobNumber();
		
		if (!$job_number) {
			return array();
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
			." ou.dbvg"
			." FROM ivimport_orgunit ou "
			." INNER JOIN ivimport_stelle stelle "
			."         ON ou.id = stelle.sql_org_unit_id "
			."        AND stelle.stellennummer = '".$job_number."' "
			;
		
		$result = mysql_query($sql, $mysql);
		$record = mysql_fetch_assoc($result);

		if (!$record) {
			return array();
		}
		
		return $record;
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

	/**
	 * Gibt eine Liste aller ObjIds von Organisationseinheiten unterhalb von UVG in 
	 * denen der Benutzer Mitglied ist.
	 *
	 * @param 	int			$a_user_id
	 * @param	array[]		obj_ids der Organisationseinheiten
	 */
	public function getUVGOrgUnitObjIdsIOf($a_user_id) {
		return array_map(function($a_ref_id) {
			return ilObject::_lookupObjId($a_ref_id);
		}
		, $this->orgu_tree->getOrgUnitOfUser($a_user_id, $this->pou->getBaseRefId()));
	}

	/**
	 * Gibt die ObjId der Organisationseinheit auf der Ebene direkt unterhalb von
	 * UVG zurück, zu der die gegebene Organisationseinheit gehört.
	 *
	 * @param	int			$a_orgu_obj_id
	 * @throws	ilException					Wenn die gegebene Organisationseinheit 
	 *										nicht zum UVG gehört.
	 * @return	int							obj_id
	 */
	public function getUVGTopLevelOrguIdFor($a_orgu_obj_id) {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$uvg_children = $this->orgu_tree->getChildren($this->pou->getBaseRefId());
		$orgu_ref_id = gevObjectUtils::getRefId($a_orgu_obj_id);
		$parent_ref_id = $this->orgu_tree->getParent($this->orgu_tree->getParent($orgu_ref_id));
		if (in_array($orgu_ref_id, $uvg_children)) {
			return $a_orgu_obj_id;
		}
		if (!in_array($parent_ref_id, $uvg_children)) {
			throw new ilException("Parent of $orgu_ref_id is no children of UVG");
		}
		return gevObjectUtils::getObjId($parent_ref_id);
	}
}

?>