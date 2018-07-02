<?php
require_once("Services/TMS/Category/Settings.php");

class ilCategoryDB {
	const TABLE_NAME = "tms_cat_settings";

	public function __construct(ilDBInterface $db) {
		$this->db = $db;
	}

	public function create($obj_id) {
		return new Settings($obj_id, false);
	}

	/**
	 * Inserts a new settings entry or updates
	 *
	 * @param \Settings 	$tms_settings
	 *
	 * @return void
	 */
	public function upsert(\Settings $settings) {
		$primaryKeys = array("obj_id" => array("integer", $settings->getObjId()));
		$columns = array("show_in_cockpit" => array("integer", $settings->getShowInCockpit()));
		$this->db->replace(self::TABLE_NAME, $primaryKeys, $columns);
	}

	/**
	 * Get the setting for object
	 *
	 * @return \Settings
	 */
	public function selectFor($obj_id) {
		$query = "SELECT show_in_cockpit".PHP_EOL
				." FROM ".self::TABLE_NAME.PHP_EOL
				." WHERE obj_id = ".$this->db->quote($obj_id, "integer");

		$res = $this->db->query($query);
		if($this->db->numRows($res) == 0) {
			return new Settings($obj_id, false);
		}

		$row = $this->db->fetchAssoc($res);

		return new Settings($obj_id, (bool)$row["show_in_cockpit"]);
	}

	/**
	 * Deletes the settings for object
	 *
	 * @param int 	$obj_id
	 *
	 * @return void
	 */
	public function deleteFor($obj_id) {
		$query = "DELETE FROM ".self::TABLE_NAME.PHP_EOL
				." WHERE obj_id = ".$this->db->quote($obj_id, "integer");
		$this->db->manipulate($query);
	}
}