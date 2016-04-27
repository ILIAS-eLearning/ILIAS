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
	public function createObjEntry($obj_id, reportSettings $settings) {
		$fields = array("id");
		$values = array($obj_id);
		foreach($settings->settingIds() as $field_id) {
			$fields[] = $field_id;
			$setting =  $settings->setting($field_id);
			$values[] = $this->quote($setting->defaultValue(), $setting);
		}
		$query = "INSERT INTO ".$this->settings->table()
				."	(".implode(",",$fields).") VALUES"
				."	(".implode(",",$values).")";
		$this->db->manipulate($query);
	}

	/**
	 *	update an object in the database
	 * 	@param	int	obj_id 
	 * 	@param	array	settings
	 */
	public function updateObjEntry($obj_id, reportSettings $settings, array $settings_data) {
		$query_parts = array();
		$fields = $this->settings->settingIds();
		if(count($fields) > 0) {
			foreach($fields as $field) {
				$setting =  $settings->setting($field);
				$query_parts[] = $field." = ".$this->quote($settings_data,$setting);
			}
			$query = " UPDATE ".$this->settings->table()." SET "
					."	".implode(",",$query_parts)
					."	WHERE id = ".$obj_id;
			$this->db->manipulate($query);
		}
	}

	/**
	 *	update an object in the database
	 * 	@param	int	obj_id 
	 * 	@return	array	settings
	 */
	public function readObjEntry($obj_id, reportSettings $settings) {
		$query = 'SELECT '.implode(', ' ,$settings->settingIds())
				.'	FROM '.$settings->table();

		return $this->db->fetchAssoc($this->db->query($query));
	}

	/**
	 *	delete an object in the database
	 * 	@param	int	obj_id 
	 */
	public function deleteObjEntry($obj_id, reportSettings $settings) {
		$query = 'DELETE FROM '.$settings->table().' WHERE id = '.$obj_id;
		$this->db->manipulate($query);
	}

	/**
	 *	use the right quoting for certain settings
	 * 	@param	int	obj_id 
	 * 	@param	array	settings
	 */
	protected function quote($value, setting $setting) {
		if($setting instanceof settingInt || $setting instanceof settingFloat || $setting instanceof settingBool ) {
			$quote_format = 'integer';
		}

		if($setting instanceof settingString || $setting instanceof settingText || $setting instanceof settingRichText ) {
			$quote_format = 'text';
		}

		return $this->db->quote($value, $quote_format);
	}

}