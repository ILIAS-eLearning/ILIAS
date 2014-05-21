<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Course seraching GUI for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");

class gevCourseUtils {
	static $instances = array();
	
	protected function __construct($a_crs_id) {
		global $ilDB;
		
		$this->db = &$ilDB;
		
		$this->crs_id = $a_crs_id;
		$this->gev_settings = gevSettings::getInstance();
	}
	
	static public function getInstance($a_crs_id) {
		if (array_key_exists($a_crs_id, self::$instances)) {
			return self::$instances[$a_crs_id];
		}

		self::$instances[$a_crs_id] = new gevCourseUtils($a_crs_id);
		return self::$instances[$a_crs_id];
	}
	
	static function getLinkTo($a_crs_id) {
		return "http://www.google.de"; //TODO: implement this properly
	}
	
	public function getLink() {
		return self::getLinkTo($this->crs_id);
	}
	
	protected function getAMDField($a_amd_setting) {
		$amd_ids = explode(" ", $this->gev_settings->get($a_amd_setting));
		
		$ret = $this->db->query("SELECT field_type FROM adv_mdf_definition WHERE field_id = ".$this->db->quote($amd_ids[1], "integer"));
		if ($res = $this->db->fetchAssoc($ret)) {
			return $this->getAMDValue($amd_ids[1], $res["field_type"]);
		}
		else {
			throw new Exception("AMD Field ".$amd_ids[1]." for GEV setting ".$a_amd_setting." does not exist.");
		}
	}
	
	protected function getAMDValue($a_field_id, $a_type) {
		switch($a_type) {
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
				return $this->getAMDSelectValue($a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
				return $this->getAMDTextValue($a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
				return $this->getAMDDateValue($a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				return $this->getAMDDateTimeValue($a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
				return $this->getAMDIntegerValue($a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_FLOAT:
				return $this->getAMDFloatValue($a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				return $this->getAMDLocationValue($a_field_id);
			default:
				throw new Exception("Can't get AMD Value of field ".$a_field_id." for type ".$a_type.".");
		}
	}
	
	protected function getAMDSelectValue($a_field_id) {
		return $this->getAMDTextValue($a_field_id);
	}

	protected function getAMDTextValue($a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_text ".
								"WHERE obj_id = ".$this->db->quote($this->crs_id, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return $ret;
		}
		return null;
	}
	
	protected function getAMDDateValue($a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_date ".
								"WHERE obj_id = ".$this->db->quote($this->crs_id, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return new ilDate($ret, IL_CAL_DATE);
		}
		return null;
	}
	
	protected function getAMDDateTimeValue($a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_datetime ".
								"WHERE obj_id = ".$this->db->quote($this->crs_id, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return new ilDateTime($ret, IL_CAL_DATETIME);
		}
		return null;
	}
	
	protected function getAMDIntegerValue($a_field_id) {
		
	}
	
	protected function getAMDFloatValue($a_field_id) {
		
	}
	
	protected function getAMDLocationValue($a_field_id) {
		
	}
	
	public function getCustomId() {
		return $this->getAMDField(gevSettings::CRS_AMD_CUSTOM_ID);
	}
}

?>