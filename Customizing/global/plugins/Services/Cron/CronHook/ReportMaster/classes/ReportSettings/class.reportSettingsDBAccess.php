<?php

class reportSettingsDataHandler {
	protected $db;
	protected $settings_format;


	public function __construct(settingsFormatContainer $settings_format, $db) {
		$this->db = $db;
		$this->settings_format = $settings_format;
	}

	/**
	 *	create an object entry in the database
	 * 	@param	int	obj_id
	 * 	@param	settingsValueContainer	settings_values
	 */
	public function createObjEntry($obj_id, settingsValueContainer $settings_values) {
		$fields = array("id");
		$values = array($obj_id);
		foreach($this->settings_format->fields() as $field_id) {
			$fields[] = $field_id;
			$values[] = $this->quote($settings_values->get($field_id),$field_id);
		}
		$query = "INSERT INTO ".$this->settings_format->table()
				."	(".implode(",",$fields).") VALUES"
				."	(".implode(",",$values)
		$this->db->query($query);
	}

	/**
	 *	update an object in the database
	 * 	@param	int	obj_id 
	 * 	@param	settingsValueContainer	settings_values
	 */
	public function updateObjEntry($obj_id, settingsValueContainer $settings_values) {
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
	 *
	 */
	public function readObj(ilObjReportBase $obj) {
		return $settins_container;
	}


	/**
	 *
	 */
	public function deleteObj(ilObjReportBase $obj) {
		$this->gIldb->manipulate("DELETE FROM ".$this->settings_format->getTable()." WHERE ".
			" id = ".$this->gIldb->quote($obj->getId(), "integer")
		); 
	}

	/**
	 *	
	 */
	protected function quote($value, $field_id) {
		$
	}

	protected function 
}