<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Manipulate and query the Org-Units in the UVG.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Modules/OrgUnit/classes/PersonalOrgUnit/class.ilPersonalOrgUnits.php");

class gevUVGOrgUnits extends ilPersonalOrgUnits {
	static $instance;
	const BD_SUB_TITLE_FINANCE = "Finanzen";
	const BD_SUB_TITLE_COMPOSITE = "Komposit";

	protected function __construct() {
		global $ilDB, $tree, $ilLog;
		$this->db = $ilDB;
		$this->tree = $tree;
		$this->gLog = $ilLog;
		
		$this->gev_settings = gevSettings::getInstance();
		
		parent::__construct( $this->gev_settings->getDBVPOUBaseUnitId()
						   , $this->gev_settings->getDBVPOUTemplateUnitId()
						   );
	}
	
	public function getBaseRefId() {
		return $this->base_ref_id;
	}
	
	public function getTemplateRefId() {
		return $this->template_ref_id;
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getClassName() {
		return "gevUVGOrgUnits";
	}

	public function createOrgUnitFor($a_superior_id) {
		$orgu = parent::createOrgUnitFor($a_superior_id);
		$this->moveToBDFromIV($orgu);
		return $orgu;
	}

	/**
	 * Moves the given personal org unit to the appropriate location in the UVG-structure,
	 * i.e. moves to a subunit where name equals the name found in iv. Throws when given
	 * org unit is no child of the base org unit.
	 * If no BD could be determinded from IV, moves org unit to base.
	 *
	 * @param iObjOrgUnit $a_orgu
	 */
	public function moveToBDFromIV(ilObjOrgUnit $a_orgu) {
		global $ilLog;
		$owner = $this->getOwnerOfOrgUnit($a_orgu->getId());

		try {
			$target_ref_id = $this->getBDOrgUnitRefIdFor($owner);
			//Erstellt und/oder sucht nach komposit oder finanz org unit
			$target_sub_ref_id = $this->getBDSubOrgUnitRefIdFor($owner, $target_ref_id);
		}
		catch (ilPersonalOrgUnitsException $excp) {
			$target_ref_id = $this->base_ref_id;
		}
		
		$ref_id = $a_orgu->getRefId();
		if (!$ref_id) {
			$this->PersonalOrgUnit(
					"moveToBDFromIV",
					"Could not find ref_id for ".$a_orgu->getId().".");
		}
		
		$this->tree->moveTree($ref_id, $target_sub_ref_id);
	}
	
	protected function getJobNumberOf($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		return gevUserUtils::getInstance($a_user_id)->getJobNumber();
	}
	
	protected function getBDOrgUnitRefIdFor($a_user_id) {
		$bd_name = $this->getBDFromIVOf($a_user_id);
		if (!$bd_name) {
			$this->ilPersonalOrgUnitsError("getBDOrgUnitRefIdFor", "Could not find BD-Name for $a_user_id.");
		}
		
		$bd_ref = gevOrgUnitUtils::getBDByName($bd_name, $this->base_ref_id);
		if(!$bd_ref) {
			return $this->createBDOrgUnit($bd_name)->getRefId();
		}

		return $bd_ref;
	}

	protected function getBDSubOrgUnitRefIdFor($user_id, $bd_org_unit_ref_id) {
		$sub_orgu_title = $this->getBDSubFromIVOf($user_id);

		if(!$sub_orgu_title) {
			$this->ilPersonalOrgUnitsError("getBDSubOrgUnitRefIdFor", "Could not find BD-SubOrgu-Name for $a_user_id.");
		}
		$children = $this->tree->getChilds($this->base_ref_id);
		foreach ($children as $child) {
			if (ilObject::_lookupTitle($child["obj_id"]) == $sub_orgu_title) {
				return $child["ref_id"];
			}
		}
		
		$sub_orgu_ref_id = $this->createBDSubOrgUnit($sub_orgu_title)->getRefId();
		$this->tree->moveTree($sub_orgu_ref_id, $bd_org_unit_ref_id);

		return $sub_orgu_ref_id;
	}
	
	public function getBDFromIVOf($a_user_id) {
		global $ilClientIniFile;
		global $ilDB;

		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass) 
				or die( "MySQL: ".mysql_error()." ### "
						." Is the shadowdb initialized?"
						." Are the settings for the shadowdb initialized in the client.ini.php?"
					  );
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$agent_key = $this->getJobNumberOf($a_user_id);

		$sql = 	 "SELECT `ivimport_orgunit`.`name`"
				."  FROM `ivimport_stelle`"
				."  INNER JOIN `ivimport_orgunit`"
				."          ON `ivimport_orgunit`.`id` = `ivimport_stelle`.`sql_org_unit_id`"
				." WHERE `ivimport_stelle`.`stellennummer` = ".$ilDB->quote($agent_key,"text");
		
		$data = mysql_query($sql);
		$data = mysql_fetch_assoc($data);

		// Shorten Name
		$name = $data["name"];
		$matches = array();
		if (preg_match("/^Generali Versicherung AG (.*)$/", $name, $matches)) {
			$name = $matches[1];
		}
		if (preg_match("/^Bereichsdirektion (.*)$/", $name, $matches)) {
			$name = "BD ".$matches[1];
		}
		return $name;
	}

	protected function getBDSubFromIVOf($user_id) {
		global $ilClientIniFile;
		global $ilDB;

		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass) 
				or die( "MySQL: ".mysql_error()." ### "
						." Is the shadowdb initialized?"
						." Are the settings for the shadowdb initialized in the client.ini.php?"
					  );
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$agent_key = $this->getJobNumberOf($user_id);

		$sql = 	 "SELECT IF(dbaf = ".$ilDB->quote($agent_key,"text").", dbaf, null) as finance\n"
					.", IF(dbvg = ".$ilDB->quote($agent_key,"text").", dbvg, null) as composite\n"
					." FROM `ivimport_orgunit`\n"
					." WHERE `dbaf` = ".$ilDB->quote($agent_key,"text")." OR `dbvg` = ".$ilDB->quote($agent_key,"text");
		
		$result = mysql_query($sql);
		$data = mysql_fetch_assoc($result);

		if($data["finance"] && $data["composite"]) {
			$this->gLog->write("gevUVGOrgUnits::getBDSubFromIVOf: DBV (ILIAS ID:".$user_id.") is Finance AND Composite. Just Finance would be created.");
		}

		if($data["finance"]) {
			return self::BD_SUB_TITLE_FINANCE;
		}

		if($data["composite"]) {
			return self::BD_SUB_TITLE_COMPOSITE;
		}

		return false;
	}
	
	
	protected function createBDOrgUnit($a_bd_name) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$orgu = new ilObjOrgUnit();
		$orgu->setTitle($a_bd_name);
		$orgu->create();
		$orgu->createReference();
		$orgu->update();
		$orgu->putInTree($this->base_ref_id);
		$orgu->initDefaultRoles();

		$orgutils = gevOrgUnitUtils::getInstance($orgu->getId());
		$orgutils->setType(gevSettings::REF_ID_ORG_UNIT_TYPE_BD);
		
		return $orgu;
	}

	protected function createBDSubOrgUnit($a_bd_name) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		
		$orgu = new ilObjOrgUnit();
		$orgu->setTitle($a_bd_name);
		$orgu->create();
		$orgu->createReference();
		$orgu->update();
		$orgu->putInTree($this->base_ref_id);
		$orgu->initDefaultRoles();

		return $orgu;
	}
}

?>