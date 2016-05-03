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
		if (!$adv) {
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
		return $this->pou->getEmployeesOf($a_adviser_id);
	}
	
	/**
	 * Entfernt die NA-Org.-Einheit für einen Benutzer.
	 *
	 * @param integer $a_adviser_id
	 */
	public function removeNAOrgUnitOf($a_adviser_id) {
		require_once("./Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		$orgu = $this->pou->getOrgUnitOf($a_adviser_id);
		$orgu_utils = gevOrgUnitUtils::getInstance($orgu->getId());
		foreach(gevOrgUnitUtils::getEmployeesIn(array($orgu->getRefId())) as $na_id) {
			$orgu_utils->deassignUser($na_id, "Mitarbeiter");
		}
		$this->pou->purgeOrgUnitOf($a_adviser_id);
	}
	
	static $ADVISER_ROLES = array(
		  "Administrator"
		, "Admin-Voll"
		, "Admin-eingeschraenkt"
		, "OD/BD"
		, "OD"
		, "BD"
		, "FD"
		, "UA"
		, "HA 84"
		, "BA 84"
		, "Org PV 59"
		, "PV 59"
		, "AVL"
		, "VA 59"
		, "VA HGB 84"
		, "NFK"
		, "int. Trainer"
		, "DBV EVG"
		);
	
	/**
	 * Sucht einen ILIAS-Benutzer, der zur Eingabe passt. Durchsucht werden
	 * Login sowie Vorname und Nachname aller Benutzer.
	 *
	 * Durchsucht zuerst das Feld login, um eine eindeutige Möglichkeit zu haben,
	 * den Betreuer einzugeben.
	 *
	 * Gibt null zurück, wenn keiner oder viele Benutzer gefunden werden, die
	 * zur Eingabe passen. Gibt ansonsten die ILIAS-Id des gefundenen Benutzers
	 * zurück.
	 *
	 * Benutzer, die die Rolle NA werden aus der Suche ausgenommen (#863)
	 */
	public function searchAdviser($a_search) {
		$query_head = 
			 "SELECT DISTINCT ud.usr_id "
			."  FROM usr_data ud"
			."  LEFT JOIN object_data od ON od.type='role' AND ".$this->db->in("od.title", self::$ADVISER_ROLES, false, "text")
			."  LEFT JOIN object_reference oref ON od.obj_id = oref.obj_id AND oref.deleted IS NULL"
			."  LEFT JOIN rbac_ua ua ON ua.usr_id = ud.usr_id AND ua.rol_id = od.obj_id"
			;
			
		$res = $this->db->query(
			 $query_head
			." WHERE ud.login = ".$this->db->quote($a_search, "text")
			."   AND NOT ua.usr_id IS NULL"
			);
		
		if ($this->db->numRows($res) === 1) {
			$rec = $this->db->fetchAssoc($res);
			return $rec["usr_id"];
		}
		
		/* #901
		$spl = explode(" ", $a_search);
		foreach($spl as $key => $value) {
			$search = $this->db->quote("%".trim($value)."%", "text");
			$spl[$key] = "( firstname LIKE ".$search." OR lastname LIKE ".$search." )";
		}
		$res = $this->db->query(
				 $query_head
				." WHERE ".implode(" AND ", $spl)
				."   AND NOT ua.usr_id IS NULL"
				);

		if ($this->db->numRows($res) === 1) {
			$rec = $this->db->fetchAssoc($res);
			return $rec["usr_id"];
		}*/
		
		return null;
	}
	
	// According to GEV_002a-SL_GOAL-Feature-Nebenberufsagenturen-Mailversand_2014-11-30.docx
	public function getNASuccessfullMailRecipient($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($a_user_id);
		if (!$user_utils->isNA()) {
			throw new Exception("gevNAUtils::getNASuccessfullMailRecipient: User $a_user_id is no NA.");
		}
		
		$od = $user_utils->getOD();
		$tmp = explode(" ", $od["title"]);
		if (in_array($tmp[1], self::$ADSN_ODS)) {
			return "ADS Nord <ads-nord@generali.com>";
		}
		if (in_array($tmp[1], self::$ADSS_ODS)) {
			return "ADS Sued <ads-sued@generali.com>";
		}
		
		return "NA Bildung <na-bildung@generali.de>";
	}
	
	static $ADSN_ODS = array(
			  "Berlin"
			, "Bremen"
			, "Dortmund"
			, "Dresden"
			, "Düsseldorf"
			, "Erfurt"
			, "Frankfurt"
			, "Gießen"
			, "Hamburg"
			, "Hannover"
			, "Köln"
			, "Münster"
			, "Saarbrücken"

		);
	
	static $ADSS_ODS = array(
			  "Bamberg"
			, "Freiburg"
			, "Heilbronn"
			, "Karlsruhe"
			, "München"
			, "Nürnberg"
			, "Passau"
			, "Ravensburg"
			, "Regensburg"
			, "Rosenheim"
			, "Stuttgart"
		);
	
	// Confirmation or denial of na accounts.
	
	public function createConfirmationToken($a_user_id, $a_adviser_id) {
		$max_attempts = 10;
		$found_token = false;
		$attempt = 0;

		while (true) {
			$token = md5(rand());
			if ($this->tokenIsUsable($token)) {
				break;
			}

			if ($attempt > $max_attempts) {
				throw new Exception("gevNAUtils::createConfirmationToken: Number of maximum attempts has been reached.");
			}
			$attempt++;
		}
		
		$this->saveToken($token, $a_user_id, $a_adviser_id);
		
		return $token;
	}

	protected function tokenIsUsable($a_token) {
		$res = $this->db->query("SELECT * FROM gev_na_tokens WHERE token = ".$this->db->quote($a_token, "text"));
		return $this->db->numRows($res) == 0;
	}
	
	protected function saveToken($a_token, $a_user_id, $a_adviser_id) {
		$this->db->manipulate("INSERT INTO gev_na_tokens (user_id, adviser_id, token)"
							 ." VALUES ( ".$this->db->quote($a_user_id, "integer")
							 ."        , ".$this->db->quote($a_adviser_id, "integer")
							 ."        , ".$this->db->quote($a_token, "text")
							 ."        )"
							 );
	}
	
	public function confirmWithToken($a_token) {
		$user_id = $this->getUserWithToken($a_token);
		if ($user_id === null) {
			return false;
		}
		
		$user = new ilObjUser($user_id);
		
		if ($user->getActive()) {
			return false;
		}
		
		$user->setActive(true, 6);
		$user->update();
		
		$adviser_id = $this->getAdviserForToken($a_token);
		
		$this->assignAdviser($user_id, $adviser_id);
		
		require_once("Services/GEV/Mailing/classes/class.gevNARegistrationMails.php");
		$na_mails = new gevNARegistrationMails( $user->getId()
											  , ""
											  , ""
											  );
				
		$na_mails->send("na_confirmed", array($user->getId()));
		
		return true;
	}
	
	public function denyWithToken($a_token) {
		$user_id = $this->getUserWithToken($a_token);
		if ($user_id === null) {
			return false;
		}
		
		$user = new ilObjUser($user_id);
		
		if ($user->getActive()) {
			return false;
		}

		require_once("Services/GEV/Mailing/classes/class.gevNARegistrationMails.php");
		$na_mails = new gevNARegistrationMails( $user->getId()
											  , ""
											  , ""
											  );
				
		$na_mails->send("na_not_confirmed", array($user->getId()));
		
		$user->delete();
		
		return true;
	}
	
	protected function getUserWithToken($a_token) {
		$res = $this->db->query( "SELECT user_id "
								."  FROM gev_na_tokens"
								."  JOIN usr_data ON usr_id = user_id"
								." WHERE token = ".$this->db->quote($a_token, "text")
								."   AND NOT login IS NULL"
								);
		if ($rec = $this->db->fetchAssoc($res)) {
			return $rec["user_id"];
		}
		return null;
	}
	
	protected function getAdviserForToken($a_token) {
		$res = $this->db->query( "SELECT adviser_id "
								."  FROM gev_na_tokens"
								."  JOIN usr_data ON usr_id = user_id"
								." WHERE token = ".$this->db->quote($a_token, "text")
								."   AND NOT login IS NULL"
								);
		if ($rec = $this->db->fetchAssoc($res)) {
			return $rec["adviser_id"];
		}
		return null;
	}
}

?>