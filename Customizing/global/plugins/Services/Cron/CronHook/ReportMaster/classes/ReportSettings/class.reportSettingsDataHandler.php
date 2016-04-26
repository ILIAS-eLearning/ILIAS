<?php

class reportSettingsDataHandler {
	protected $db;
	protected $settings_format;


	public function __construct($db) {
		$this->db = $db;
		$this->settings_format = $settings_format;
	}

	/**
	 *	create an object entry in the database
	 * 	@param	int	obj_id
	 * 	@param	settingsValueContainer	settings_values
	 */
	public function createObjEntry($obj_id, reportSettings $settings_format) {
		$fields = array("id");
		$values = array($obj_id);
		foreach($settings_format->settingFormatIds() as $field_id) {
			$fields[] = $field_id;
			$setting =  $settings_format->settingFormat($field_id);
			$values[] = $this->quote($setting->defaultValue(), $setting);
		}
		$query = "INSERT INTO ".$this->settings_format->table()
				."	(".implode(",",$fields).") VALUES"
				."	(".implode(",",$values).")";
		$this->db->query($query);
	}

	/**
	 *	update an object in the database
	 * 	@param	int	obj_id 
	 * 	@param	array	settings
	 */
	public function updateObjEntry($obj_id, array $settings) {
		$query_parts = array();
		foreach($this->settings_format->fields() as $field_id) {
			$query_parts[] = $field_id." = ".$this->quote($settings_values->get($field_id),$field_id);
		}
		$query = " UPDATE ".$this->settings_format->table()." SET "
				."	".implode(",",$query_parts)
				."	WHERE id = ".$obj_id;
		$this->db->query($query);

	}

	/**
	 *	update an object in the database
	 * 	@param	int	obj_id 
	 * 	@return	array	settings
	 */
	public function readObjEntry($obj_id) {
		return $settings;
	}

	/**
	 *	delete an object in the database
	 * 	@param	int	obj_id 
	 */
	public function deleteObjEntry($obj_id) {

	}

	/**
	 *	update an object in the database
	 * 	@param	int	obj_id 
	 * 	@param	array	settings
	 */
	protected function quote($value, setting $setting) {
		if($setting instanceof settingInt) {
			$quote_format = 'integer';
		}

		return $this->db->quote($value);
	}

}