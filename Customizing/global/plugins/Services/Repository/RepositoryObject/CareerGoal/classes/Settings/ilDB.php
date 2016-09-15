<?php
namespace CaT\Plugins\CareerGoal\Settings;

class ilDB implements DB {
	const PLUGIN_TABLE = "rep_obj_xcgo";

	public function __construct($db, $user) {
		$this->db = $db;
		$this->user = $user;
	}

	/**
	 * @inheritdoc
	 */
	public function install() {
		$this->pluginTable();
	}

	/**
	 * @inheritdoc
	 */
	public function create($obj_id, $lowmark, $should_specifiaction, $default_text_failed, $default_text_partial, $default_text_success) {
		$settings = new CareerGoal($obj_id, $lowmark, $should_specifiaction, $default_text_failed, $default_text_partial, $default_text_success);

		$values = array
				( "obj_id" => array("integer", $settings->getObjId())
				, "lowmark" => array("float", $settings->getLowmark())
				, "should_specifiaction" => array("float", $settings->getShouldSpecification())
				, "default_text_failed" => array("text", $settings->getDefaultTextFailed())
				, "default_text_partial" => array("text", $settings->getDefaultTextPartial())
				, "default_text_success" => array("text", $settings->getDefaultTextSuccess())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				);
		$this->getDB()->insert(self::PLUGIN_TABLE, $values);

		return $settings;
	}

	/**
	 * @inheritdoc
	 */
	public function update(CareerGoal $settings) {
		$values = array
				( "lowmark" => array("float", $settings->getLowmark())
				, "should_specifiaction" => array("float", $settings->getShouldSpecification())
				, "default_text_failed" => array("text", $settings->getDefaultTextFailed())
				, "default_text_partial" => array("text", $settings->getDefaultTextPartial())
				, "default_text_success" => array("text", $settings->getDefaultTextSuccess())
				, "last_change" => array("text", date("Y-m-d H:i:s"))
				, "last_change_user" => array("integer", $this->user->getId())
				);

		$where = array
				( "obj_id" => array("integer", $settings->getObjId())
				);

		$this->getDB()->update(self::PLUGIN_TABLE, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($obj_id) {
		assert('is_int($obj_id)');

		$delete = "DELETE FROM ".self::PLUGIN_TABLE." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");
		$this->getDB()->manipulate($delete);
	}

	/**
	 * @inheritdoc
	 */
	public function select($obj_id) {
		assert('is_int($obj_id)');

		$select = "SELECT lowmark, should_specifiaction, default_text_failed, default_text_partial, default_text_success\n"
				." FROM ".self::PLUGIN_TABLE."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($select);
		$row = $this->getDB()->fetchAssoc($res);

		if(empty($row)) {
			throw new \InvalidArgumentException("Invalid id '$obj_id' for CareerGoal-object");
		}

		$settings = new CareerGoal((int)$obj_id
								 , (float)$row["lowmark"]
								 , (float)$row["should_specifiaction"]
								 , $row["default_text_failed"]
								 , $row["default_text_partial"]
								 , $row["default_text_success"]
							);

		return $settings;
	}

	protected function pluginTable() {
		if(!$this->getDB()->tableExists(self::PLUGIN_TABLE)) {
			$fields = 
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'lowmark' => array(
						'type' 		=> 'float',
						'notnull' 	=> true
					),
					'should_specifiaction' => array(
						'type' 		=> 'float',
						'notnull' 	=> true
					),
					'default_text_failed' => array(
						'type' 		=> 'clob',
						'notnull' 	=> true
					),
					'default_text_partial' => array(
						'type' 		=> 'clob',
						'notnull' 	=> true
					),
					'default_text_success' => array(
						'type' 		=> 'clob',
						'notnull' 	=> true
					),
					'last_change' => array(
						'type' 		=> 'timestamp',
						'notnull' 	=> true
					),
					'last_change_user' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::PLUGIN_TABLE, $fields);
			$this->getDB()->addPrimaryKey(self::PLUGIN_TABLE, array("obj_id"));
		}
	}

	public function getCareerGoalDefaultText($obj_id) {
		$obj = $this->select($obj_id);
		$ret = array();
		$ret["default_text_failed"] = $obj->getDefaultTextFailed();
		$ret["default_text_partial"] = $obj->getDefaultTextPartial();
		$ret["default_text_success"] = $obj->getDefaultTextSuccess();

		return $ret;
	}

	protected function getDB() {
		if(!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}