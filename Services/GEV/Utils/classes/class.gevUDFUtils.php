<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for AdvancedMetadata of Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

class gevUDFUtils {
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
		
		self::$instance = new gevUDFUtils();
		return self::$instance;
	}
	
	public function getField($a_usr_id, $a_udf_setting) {
		$field_id = $this->gev_settings->getUDFFieldId($a_udf_setting);
		$field_type = $this->getFieldType($field_id);
		
		if (in_array($field_type, array(UDF_TYPE_WYSIWYG))) {
			throw new Exception("gevUDFUtils::getField: type '".$field_type."' not supported right now.");
		}
		
		$res = $this->db->query("SELECT value FROM udf_text ".
								" WHERE usr_id = ".$this->db->quote($a_usr_id, "integer").
								"   AND field_id = ".$this->db->quote($field_id, "integer")
								);
		
		if ($rec = $this->db->fetchAssoc($res)) {
			return $rec["value"];
		}
		return "";
	}
	
	public function setField($a_usr_id, $a_udf_setting, $a_value) {
		$field_id = $this->gev_settings->getUDFFieldId($a_udf_setting);
		$field_type = $this->getFieldType($field_id);
		
		if (in_array($field_type, array(UDF_TYPE_WYSIWYG))) {
			throw new Exception("gevUDFUtils::getField: type '".$field_type."' not supported right now.");
		}

		$this->db->manipulate("INSERT INTO udf_text (usr_id, field_id, value)".
							  " VALUES ( ".$this->db->quote($a_usr_id, "integer").
							  "        , ".$this->db->quote($field_id, "integer").
							  "        , ".$this->db->quote($a_value, "text").
							  "        )".
							  " ON DUPLICATE KEY UPDATE value = ".$this->db->quote($a_value, "text")
							  );
	}
	
	public function getFieldType($a_field_id) {
		$res = $this->db->query("SELECT field_type FROM udf_definition ".
								" WHERE field_id = ".$this->db->quote($a_field_id, "integer")
								);
		
		if ($rec = $this->db->fetchAssoc($res)) {
			return intval($rec["field_type"]);
		}
		
		throw new Exception("gevUDFUtils::getFieldType: Unknown field with id '".$a_field_id."'.");
	}
	
	/**
	 * Create a set of udf_fields.
	 * 
	 * Expects an array $a_fields of the form:
	 * array( $field_name => array( $setting	// gev-setting constant to store field id
	 *							  , $type		// valid types defined in 
	 *											// Services/User/classes/class.ilUserDefinedFields.php
	 *							  , $access		// array with key => bool, where valid keys are defined in
	 *											// Services/User/classes/class.ilUDFClaimingPlugin.php, l.122 f
	 *							  , $options	// only for $type == UDF_TYPE_SELECT, where $options is an array
	 * 											// containing the selectable options
	 *							  )
	 *	    )
	 *
	 * Returns field ids of created fields.
	 */
	public static function createUDFFields($a_fields) {
		require_once("Services/User/classes/class.ilUDFClaimingPlugin.php");
		
		$gev_set = gevSettings::getInstance();
		$field_ids = array();
		
		foreach ($a_fields as $title => $field) {
			$field_id = ilUDFClaimingPlugin::createDBField($field[1], $title, $field[2], $field[3]);
			$gev_set->set($field[0], $field_id);
			$field_ids[] = $field_id;
		}
		return $field_ids;
	}
	
	/**
	 * Update a set of udf_fields.
	 *
	 * Expects an array of the form
	 * array( $setting => array($title, $access, $options) )
	 *
	 */
	public static function updateUDFFields($a_fields) {
		require_once("Services/User/classes/class.ilUDFClaimingPlugin.php");
		
		$gev_set = gevSettings::getInstance();
		
		foreach ($a_fields as $id => $field) {
			$field_id = $gev_set->getUDFFieldId($id);
			ilUDFClaimingPlugin::updateDBField($field_id, $field[0], $field[1], $field[2]);
		}
	}

	
	/**
	 * removes a udf_field.
	 * 
	 */
	public static function removeUDFField($a_udf_id) {
		require_once("Services/User/classes/class.ilUDFClaimingPlugin.php");
		$gev_set = gevSettings::getInstance();
		$field_id = $gev_set->getUDFFieldId($a_udf_id);
		ilUDFClaimingPlugin::deleteDBField($field_id);
	}

		
	/**
	 * renames a udf_field.
	 * 
	 */
	public static function renameUDFField($a_udf_id, $a_name) {
		//require_once("Services/User/classes/class.ilUDFClaimingPlugin.php");
		$gev_set = gevSettings::getInstance();
		$field_id = $gev_set->getUDFFieldId($a_udf_id);

		//ilUDFClaimingPlugin::updateDBField($field_id, $a_name);
		//lUDFClaimingPlugin::updateDBField does not preserve (selection)-values.
		global $ilDB;
		$sql = "UPDATE udf_definition SET field_name = '$a_name' WHERE field_id=$field_id";
		$ilDB->manipulate($sql);
	}







}

?>