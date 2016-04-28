<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.reportSettingsException.php';

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
		$query = "INSERT INTO ".$settings->table()
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
		$fields = $settings->settingIds();
		if(count($fields) > 0) {
			foreach($fields as $field) {
				$setting =  $settings->setting($field);
				$query_parts[] = $field." = ".$this->quote($settings_data[$field],$setting);
			}
			$query = " UPDATE ".$settings->table()." SET "
					."	".implode(",",$query_parts)
					."	WHERE id = ".$obj_id;
			$this->db->manipulate($query);
		}
	}

	/**
	 *	load object settings from database
	 * 	@param	int	obj_id 
	 * 	@param	reportSettings	settings
	 *	@return	mixed[] 
	 */
	public function readObjEntry($obj_id, reportSettings $settings) {
		$query = 'SELECT '.implode(', ' ,$settings->settingIds())
				.'	FROM '.$settings->table()
				.'	WHERE id = '.$obj_id;

		return $this->db->fetchAssoc($this->db->query($query));
	}

	/**
	 *	delete an object in the database
	 * 	@param	int	obj_id
	 * 	@param	reportSettings	settings
	 */
	public function deleteObjEntry($obj_id, reportSettings $settings) {
		$query = 'DELETE FROM '.$settings->table().' WHERE id = '.$obj_id;
		$this->db->manipulate($query);
	}

	/**
	 *	use the right quoting for certain settings
	 * 	@param	mixed 	$value
	 * 	@param	setting	$settings
	 */
	protected function quote($value, setting $setting) {
		if($setting instanceof settingInt || $setting instanceof settingBool ) {
			$quote_format = 'integer';
		} elseif($setting instanceof settingFloat ) {
			$quote_format = 'float';
		} elseif($setting instanceof settingString || $setting instanceof settingText || $setting instanceof settingRichText ) {
			$quote_format = 'text';
		} else {
			throw new reportSettingsException("unknown setting type".get_class($setting));
		}

		return $this->db->quote($value, $quote_format);
	}

}