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
		$field_id = $this->getFieldId($a_amd_setting);
		try {
			$field_type = $this->getFieldType($field_id);
		}
		catch (Exception $e) {
			throw new Exception("AMD Field ".$field_id." for GEV setting ".$a_amd_setting." does not exist.");
		}
		
		return $this->getValue($a_obj, $field_id, $field_type);
	}
	
	public function setField($a_obj, $a_amd_setting, $a_value) {
		$field_id = $this->getFieldId($a_amd_setting);
		
		try {
			$field_type = $this->getFieldType($field_id);
		}
		catch (Exception $e) {
			throw new Exception("AMD Field ".$field_id." for GEV setting ".$a_amd_setting." does not exist.");
		}
		
		if ($a_value !== null && $a_value != "") {
			$this->setValue($a_obj, $field_id, $field_type, $a_value);
		}
		else {
			$this->deleteValue($a_obj, $field_id, $field_type);
		}
	}
	
	public function getTable($a_objs, $a_amd_settings, $a_additional_fields = array(), $a_additional_joins = array(), $a_additional_where = "") {
		if (count($a_objs) == 0) {
			return array();
		}

		$field_ids = array_map( array("gevAMDUtils", "getFieldId"), array_keys($a_amd_settings));
		$types = $this->getFieldTypes($field_ids);
		$query_parts = gevAMDUtils::makeQueryParts($field_ids, $types, array_values($a_amd_settings));
		
		$query = "SELECT od.obj_id, od.title, ".implode(", ", array_merge($query_parts[0], $a_additional_fields))."\n".
				 "  FROM object_data od\n".
				 implode("\n", array_merge($query_parts[1], $a_additional_joins))."\n".
				 "WHERE ".$this->db->in("od.obj_id", $a_objs, false, "integer")." ".$a_additional_where;

		$res = $this->db->query($query);
		return $this->makeTableResult($res, $field_ids, $types, $a_amd_settings);
	}

	public function getFieldId($a_amd_setting) {
		return $this->gev_settings->getAMDFieldId($a_amd_setting);
		//$amd_id = explode(" ", $this->gev_settings->get($a_amd_setting));
		//return $amd_id[1];
	}
	
	protected function getFieldType($a_field_id) {

		$ret = $this->db->query("SELECT field_type FROM adv_mdf_definition WHERE field_id = ".
								$this->db->quote($a_field_id, "integer"));
		
		if ($res = $this->db->fetchAssoc($ret)) {
			return $res["field_type"];
		}
		else {
			throw new Exception("Could not find type for field ".$a_field_id);
		}
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
		$postfix = self::getTablePostfixForType($a_type);
		return "LEFT JOIN adv_md_values_".$postfix." ".$a_name	." ON ".$a_name.".field_id = ".$a_field_id." AND ".$a_name.".obj_id = od.obj_id";
	}
	
	protected static function getTablePostfixForType($a_type) {
				switch($a_type) {
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_VENUE_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_PROVIDER_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_TEP_ORGU_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_LONG_TEXT:
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
			case ilAdvancedMDFieldDefinition::TYPE_SCHEDULE:
				return "text";
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
				return "date";
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				return "datetime";
			case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
				return "int";
			case ilAdvancedMDFieldDefinition::TYPE_FLOAT:
				return "float";
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				return "location";
			default:
				throw new Exception("gevAMDUtils::getTablePostfixForType: unknown type ".$a_type." for field ".$a_name.".");
		}
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
			case ilAdvancedMDFieldDefinition::TYPE_VENUE_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_PROVIDER_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_TEP_ORGU_SELECT:
				return $a_value;
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_LONG_TEXT:
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
				return $a_value;
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
				return $a_value;
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
				if ($a_value) {
					return new ilDate($a_value, IL_CAL_DATE);
				}
				else {
					return null;
				}
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				if ($a_value) {
					return new ilDateTime($a_value, IL_CAL_DATE);
				}
				else {
					return null;
				}
			case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
				// TODO: do a check similar to TYPE_DATETIME here?
				return intval($a_value);
			case ilAdvancedMDFieldDefinition::TYPE_FLOAT:
				// TODO: do a check similar to TYPE_DATETIME here?
				return floatval($a_value);
			case ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_SCHEDULE:
				// TODO: do a check similar to TYPE_DATETIME here?
				return unserialize($a_value);
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				die("gevAMDUtils::canonicalTransformTypedValue: Location not implemented.");
			default:
				throw new Exception("gevAMDUtils::canonicalTransformTypedValue: Can't get AMD Value of field ".$a_field_id." for type ".$a_type.".");
		}
	}
	
	protected function getValue($a_obj, $a_field_id, $a_type) {
		if ($type == TYPE_LOCATION) {
			die("gevAMDUtils::getValue not implemented for locations.");
		}
		
		$postfix = self::getTablePostfixForType($a_type);
		
		$res = $this->db->query("SELECT value FROM adv_md_values_".$postfix." ".
								"WHERE obj_id = ".$this->db->quote($a_obj, "integer").
								"  AND field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($ret = $this->db->fetchAssoc($res)) {
			return self::canonicalTransformTypedValue($a_type, $ret["value"]);
		}
		return null;
	}
	
	protected function setValue($a_obj, $a_field_id, $a_type, $a_value) {
		if ($type == TYPE_LOCATION) {
			die("gevAMDUtils::setValue not implemented for locations.");
		}
		
		$postfix = self::getTablePostfixForType($a_type);
		$value = $this->getSQLInsertValue($a_type, $a_value);
		
		// Query for subtype to set it correctly.
		$res = $this->db->query( "SELECT sub_type"
								."  FROM adv_md_record_objs r"
								."  JOIN adv_mdf_definition d ON r.record_id = d.record_id"
								." WHERE d.field_id = ".$this->db->quote($a_field_id, "integer")
								);
		if (!($rec = $this->db->fetchAssoc($res))) {
			throw new Exception("gevAMDUtils::setValue: Could not determine subtype of field with id ".$a_field_id);
		}
		
		// Special treatment for orgus, due to subtype.
		if ($rec["sub_type"] == "orgu_type") {
			$res2 = $this->db->query("SELECT orgu_type_id"
									."  FROM orgu_data"
									." WHERE orgu_id = ".$this->db->quote($a_obj, "integer")
									);
			if (!($rec2 = $this->db->fetchAssoc($res2))) {
				throw new Exception("gevAMDUtils::setValue: Could not determine type of orgu with id ".$a_obj);
			}
			$rec["sub_id"] = $rec2["orgu_type_id"];
		}
		else {
			$rec["sub_id"] = 0;
		}
		
		$this->db->manipulate("INSERT INTO adv_md_values_".$postfix.
							  " (obj_id, field_id, value, disabled, sub_type, sub_id)".
							  " VALUES (".$this->db->quote($a_obj, "integer").
							  "        ,".$this->db->quote($a_field_id, "integer").
							  "        ,".$value.
							  "        ,".$this->db->quote(0, "integer").
							  "        ,".$this->db->quote($rec["sub_type"], "text").
							  "        ,".$this->db->quote($rec["sub_id"], "integer").
							  "        ) ".
							  " ON DUPLICATE KEY UPDATE value = ".$value
							 );
	}
	
	protected function deleteValue($a_obj, $a_field_id, $a_type) {
		$postfix = self::getTablePostfixForType($a_type);
		$this->db->manipulate("DELETE FROM adv_md_values_".$postfix.
							  " WHERE obj_id = ".$this->db->quote($a_obj, "integer").
							  "   AND field_id = ".$this->db->quote($a_field_id, "integer")
							 );
	}
	
	protected function getSQLInsertValue($a_type, $a_value) {
		switch($a_type) {
			case ilAdvancedMDFieldDefinition::TYPE_VENUE_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_PROVIDER_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_TEP_ORGU_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_SELECT:
			case ilAdvancedMDFieldDefinition::TYPE_LONG_TEXT:
			case ilAdvancedMDFieldDefinition::TYPE_TEXT:
				return $this->db->quote($a_value, "text");
			case ilAdvancedMDFieldDefinition::TYPE_DATE:
				return $this->db->quote($a_value->get(IL_CAL_DATE), "date");
			case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
				return $this->db->quote($a_value->get(IL_CAL_DATETIME), "text");
			case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
				return $this->db->quote($a_value, "integer");
			case ilAdvancedMDFieldDefinition::TYPE_FLOAT:
				return $this->db->quote($a_value, "float");
			case ilAdvancedMDFieldDefinition::TYPE_MULTI_SELECT:
				return $this->db->quote(serialize($a_value), "text");
			case ilAdvancedMDFieldDefinition::TYPE_SCHEDULE:
				return $this->db->quote(serialize($a_value), "text");
			case ilAdvancedMDFieldDefinition::TYPE_LOCATION:
				die("gevAMDUtils::canonicalTransformTypedValue: Location not implemented.");
			default:
				throw new Exception("gevAMDUtils::getSQLInsertValue: Can't get AMD Value of field ".$a_field_id." for type ".$a_type.".");
		}
	}
	
	// For select or multi select amd fields get an array with all options
	// of the field.
	public function getOptions($a_amd_setting) {
		// TODO: There should be some checking of amd field type here.
		
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings = gevSettings::getInstance();
		$field_id = $settings->getAMDFieldId($a_amd_setting);
		$res = $this->db->query("SELECT field_values FROM adv_mdf_definition ".
							" WHERE field_id = ".$this->db->quote($field_id, "integer"));
		if ($rec = $this->db->fetchAssoc($res)) {
			$vals = unserialize($rec["field_values"]);
			$ret = array();
			foreach ($vals as $val) {
				$ret[$val] = $val;
			}
			return $ret;
		}
		else {
			throw new ilException("gevCourseUtils::getTypeOptions: There's something seriously wrong.");
		}
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

	public static function removeAMDRecord($a_record_title) {
		require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");

		global $ilDB;
		$gev_set = gevSettings::getInstance();
		$result = $ilDB->query("SELECT record_id FROM adv_md_record WHERE title = ".$ilDB->quote($a_record_title, "text"));
		if ($record = $ilDB->fetchAssoc($result)) {
			ilAdvancedMDClaimingPlugin::deleteDBRecord($record["record_id"]);
		}
		else {
			throw new Exception("gevAMDUtils::removeAMDRecord: No record_id found for title '".$a_record_title."'.");
		}

	}
	
	public static function addAMDField($a_record_title, $a_title, $a_gev_setting, $a_desc, $a_searchable, $a_def, $a_type) {
		require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
		
		global $ilDB;
		$gev_set = gevSettings::getInstance();
		$result = $ilDB->query("SELECT record_id FROM adv_md_record WHERE title = ".$ilDB->quote($a_record_title, "text"));
		
		if ($record = $ilDB->fetchAssoc($result)) {
			$f_id = ilAdvancedMDClaimingPlugin::createDBField($record["record_id"], $a_type, $a_title, $a_desc, $a_searchable, $a_def);
			$gev_set->set($a_gev_setting, $record["record_id"]." ".$f_id);
		}
		else {
			throw new Exception("gevAMDUtils::addAMDField: No record_id found for title '".$a_record_title."'.");
		}
	}
	
	public static function removeAMDField($a_gev_setting) {
		require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
		
		$gev_set = gevSettings::getInstance();
		$field_id = $gev_set->getAMDFieldId($a_gev_setting);
		ilAdvancedMDClaimingPlugin::deleteDBField($field_id);
	}

	public static function updateTitleOfAMDField($a_gev_setting, $title, $description) {
		require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
		global $ilDB;
		
		$gev_set = gevSettings::getInstance();
		$field_id = $gev_set->getAMDFieldId($a_gev_setting);

		$query = "UPDATE adv_mdf_definition SET 
			title = '$title',
			 description = '$description' 
			 WHERE field_id= $field_id";

		$ilDB->manipulate($query);
	}

	public static function updateOptionsOfAMDField($a_gev_setting, $options) {
		require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
		global $ilDB;

		$gev_set = gevSettings::getInstance();
		$field_id = $gev_set->getAMDFieldId($a_gev_setting);

		$field_values = serialize($options);

		$query = "UPDATE adv_mdf_definition SET 
			field_values = '$field_values'
			 WHERE field_id= $field_id";

		$ilDB->manipulate($query);
	}

	/*
	*
	* @param $a_gev_settings = array()
	*
	*/
	public static function updatePositionOrderAMDField($a_gev_settings) {
		require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
		global $ilDB, $ilLog;

		$ilLog->write("guck guck ich mach jetzt ein update");
		$gev_set = gevSettings::getInstance();

		foreach($a_gev_settings as $key => $gev_setting) {		
			$field_id = $gev_set->getAMDFieldId($gev_setting);

			$query = "UPDATE adv_mdf_definition SET"
					 ." position = ".($key+1)
					 ." WHERE field_id = $field_id";

			$ilDB->manipulate($query);
			$ilLog->write($query);
		}
	}
}
