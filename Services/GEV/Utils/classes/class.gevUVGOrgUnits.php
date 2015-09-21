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

	protected function __construct() {
		global $ilDB, $tree;
		$this->db = $ilDB;
		$this->tree = $tree;
		
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
		
		$this->tree->moveTree($ref_id, $target_ref_id);
	}
	
	protected function getJobNumberOf($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		return gevUserUtils::getInstance($a_user_id)->getJobNumber();
	}
	
	protected function getBDOrgUnitRefIdFor($a_user_id) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$bd_name = $this->getBDFromIVOf($a_user_id);
		if (!$bd_name) {
			$this->ilPersonalOrgUnitsError("getBDOrgUnitRefIdFor", "Could not find BD-Name for $a_user_id.");
		}
		$children = $this->tree->getChilds($this->base_ref_id);
		foreach ($children as $child) {
			if (ilObject::_lookupTitle($child["obj_id"]) == $bd_name) {
				return $child["ref_id"];
			}
		}
		
		// Apparently there is no org unit beneath the base that matches the desired name.
		// We need to create a new one
		return $this->createBDOrgUnit($bd_name)->getRefId();
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
	
	
	protected function createBDOrgUnit($a_bd_name) {
		require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		
		$orgu = new ilObjOrgUnit();
		$orgu->setTitle($a_bd_name);
		$orgu->create();
		$orgu->createReference();
		$orgu->update();
		$orgu->putInTree($this->base_ref_id);
		$orgu->initDefaultRoles();
		
		$orgutils = gevOrgUnitUtils::getInstance($orgu->getId());
		$orgutils->setType(gevSettings::ORG_TYPE_DEFAULT);
		
		return $orgu;
	}
}

?>