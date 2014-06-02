<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for AdvancedMetadata of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/Calendar/classes/class.ilDateTime.php");

class gevAMDUtils {
	static $instance = null;
	
	protected function __construct() {
		global $ilDB;
		$this->db = &$ilDB;

		$this->gev_settings = gevSettings::getInstance();
	}
	
	static public function getInstance() {
		if (self::$instance !== null) {
			return self::$instance;
		}
		
		self::$instance = new gevAMDUtils();
		return self::$instance;
	}
	
	public function getField($a_obj, $a_amd_setting) {
		$field_id = self::getFieldId($a_amd_setting);
		
		$ret = $this->db->query("SELECT field_type FROM adv_mdf_definition WHERE field_id = ".$this->db->quote($field_id, "integer"));
		if ($res = $this->db->fetchAssoc($ret)) {
			return $this->getValue($a_obj, $field_id, $res["field_type"]);
		}
		else {
			throw new Exception("AMD Field ".$field_id." for GEV setting ".$a_amd_setting." does not exist.");
		}
	}
	
	public function getTable($a_objs, $a_amd_settings) {
		$field_ids = array_map( array("gevAMDUtils", "getFieldId"), array_keys($a_amd_settings));
		$types = $this->getFieldTypes($field_ids);
		$query_parts = gevAMDUtils::makeQueryParts($field_ids, $types, array_values($a_amd_settings));
		
		$query = "SELECT od.obj_id, od.title, ".implode(", ", $query_parts[0])."\n".
				 "  FROM object_data od\n".
				 implode("\n", $query_parts[1])."\n".
				 "WHERE ".$this->db->in("od.obj_id", $a_objs, false, "integer");

		//die($query);	
		$res = $this->db->query($query);

		return $this->makeTableResult($res, $field_ids, $types, $a_amd_settings);
	}
	
	protected function getFieldId($a_amd_setting) {
		$amd_id = explode(" ", $this->gev_settings->get($a_amd_setting));
		return $amd_id[1];
	}
	
	protected function getFieldTypes($field_ids) {
		$res = $this->db->query("SELECT field_id, field_type FROM adv_mdf_definition ".
								"WHERE ".$this->db->in("field_id", $field_ids, false, "integer"));
		$types = array();
		while ($val = $this->db->fetchAssoc($res)) {
			$types[$val["field_id"]] = $val["field_type"];
		}
		return $types;
	}
	
	protected static function makeQueryParts($a_field_ids, $a_types, $a_names) {
		$res = array(array(), array());
		
		$count = 0;
		
		foreach ($a_field_ids as $id) {
			$name = "amd".$count;
			$res[0][] = gevAMDUtils::makeSelectPart($name, $a_types[$id], $a_names[$count]);
			$res[1][] = gevAMDUtils::makeJoinPart($name, $id, $a_types[$id]);
						
			$count += 1;
		}
		return $res;
	}
	
	protected static function makeSelectPart($a_name, $a_type, $a_out_name) {
		switch($a_type) {
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				return ("(".$a_name.".loc_lat, ".$a_name.".long_lat, ".$a_name.".loc_zoom) as ".$a_out_name);
			default:
				return $a_name.".value as ".$a_out_name;
		}
	}
	
	protected static function makeJoinPart($a_name, $a_field_id, $a_type) {
		switch($a_type) {
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
				$postfix = "text";
				break;
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
				$postfix = "date";
				break;
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				$postfix = "datetime";
				break;
			case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
				$postfix = "int";
				break;
			case ilAdvancedMDFieldDefinition::TYPE_FLOAT:
				$postfix = "float";
				break;
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				$postfix = "location";
				break;
			default:
				throw new Exception("gevAMDUtils::makeJoinPart: unknown type ".$a_type.".");
		}
		
		return "LEFT JOIN adv_md_values_".$postfix." ".$a_name	." ON ".$a_name.".field_id = ".$a_field_id." AND ".$a_name.".obj_id = od.obj_id";
	}
	
	protected function makeTableResult($a_res, $a_field_ids, $a_types, $a_amd_settings) {
		$ret = array();
		
		$field_names = array_values($a_amd_settings);
		$num_fields = count($a_types);
		
		while ($res = $this->db->fetchAssoc($a_res)) {
			for ($i = 0; $i < $num_fields; ++$i) {
				$field_name = $field_names[$i];
				$field_id = $a_field_ids[$i];
				$res[$field_name] = gevAMDUtils::canonicalTransformTypedValue($a_types[$field_id], $res[$field_name]);
			}
			
			$ret[$res["obj_id"]] = $res;
		}

		return $ret;
	}
	
	protected static function canonicalTransformTypedValue($a_type, $a_value) {
		switch($a_type) {
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
				return $a_value;
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
				return $a_value;
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
				return new ilDate($a_value, IL_CAL_DATE);
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				return new ilDateTime($a_value, IL_CAL_DATETIME);
			case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
				return intval($a_value);
			case ilAdvancedMDFieldDefinition::TYPE_FLOAT:
				return floatval($a_value);
			case ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT:
				return unserialize($a_value);
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				die("gevAMDUtils::canonicalTransformTypedValue: Location not implemented.");
			default:
				throw new Exception("gevAMDUtils::getValue: Can't get AMD Value of field ".$a_field_id." for type ".$a_type.".");
		}
	}
	
	protected function getValue($a_obj, $a_field_id, $a_type) {
		switch($a_type) {
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
				return $this->getSelectValue($a_obj, $a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
				return $this->getTextValue($a_obj, $a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
				return $this->getDateValue($a_obj, $a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				return $this->getDateTimeValue($a_obj, $a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
				return $this->getIntegerValue($a_obj, $a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_FLOAT:
				return $this->getFloatValue($a_obj, $a_field_id);
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				return $this->getLocationValue($a_obj, $a_field_id);
			default:
				throw new Exception("gevAMDUtils::getValue: Can't get AMD Value of field ".$a_field_id." for type ".$a_type.".");
		}
	}
	
	protected function getSelectValue($a_obj, $a_field_id) {
		return $this->getTextValue($a_obj, $a_field_id);
	}
	
	// TODO: Make those methods use canonicalTransformTypedValue

	protected function getTextValue($a_obj, $a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_text ".
								"WHERE obj_id = ".$this->db->quote($a_obj, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return $ret["value"];
		}
		return null;
	}
	
	protected function getDateValue($a_obj, $a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_date ".
								"WHERE obj_id = ".$this->db->quote($a_obj, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return new ilDate($ret["value"], IL_CAL_DATE);
		}
		return null;
	}
	
	protected function getDateTimeValue($a_obj, $a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_datetime ".
								"WHERE obj_id = ".$this->db->quote($a_obj, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return new ilDateTime($ret["value"], IL_CAL_DATETIME);
		}
		return null;
	}
	
	protected function getIntegerValue($a_obj, $a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_int ".
								"WHERE obj_id = ".$this->db->quote($a_obj, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return intval($ret["value"]);
		}
		return null;
	}
	
	protected function getFloatValue($a_obj, $a_field_id) {
		$res = $this->db->query("SELECT value FROM adv_md_values_float ".
								"WHERE obj_id = ".$this->db->quote($a_obj, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return floatval($ret["value"]);
		}
		return null;
	}
	
	protected function getLocationValue($a_obj, $a_field_id) {
		$res = $this->db->query("SELECT loc_lat, loc_long, loc_zoom FROM adv_md_values_location ".
								"WHERE obj_id = ".$this->db->quote($a_obj, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return array( "longitude" => floatval($ret["value"]["loc_long"])
						, "latitude" => floatval($ret["value"]["loc_lat"])
						, "zoom" => intval($ret["value"]["loc_zoom"])
						);
		}
		return null;
	}
	
	/**
	 * Create a set of amd records.
	 * 
	 * Expects an array $a_records of the form:
	 * array( $record_name => array( $record_description 
	 *						  array( $field_name =>
	 *									array( gevSettings::AMD_NAME
	 *										 , $description
	 *										 , $searchable
	 *										 , $definition (according to ADTs)
	 *										 , $type (according to AMD)
	 *										 )
	 *							   ))
	 *      )
	 * 
	 * Types is a list of types from the set crs, orgu, cat
	 *
	 * If orgu is in types then the $a_subtypes are used to assign the records to 
	 * org unit types.
	 */
	public static function createAMDRecords($a_records, $a_types, $a_subtypes = null) {
		require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
		
		$gev_set = gevSettings::getInstance();
		$record_ids = array();
		foreach($a_records as $rt => $rdef) {
			$rec_id = ilAdvancedMDClaimingPlugin::createDBRecord($rt, $rdef[0], true, $a_types);
			foreach($rdef[1] as $ft => $fdef) {
				$f_id = ilAdvancedMDClaimingPlugin::createDBField($rec_id, $fdef[4], $ft, $fdef[1], $fdef[2], $fdef[3]);
				$gev_set->set($fdef[0], $rec_id." ".$f_id);
			}
			$record_ids[] = $rec_id; 
		}
		return $record_ids;
	}
}

?>