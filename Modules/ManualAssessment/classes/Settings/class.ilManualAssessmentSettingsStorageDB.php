<?php
require_once 'Modules/ManualAssessment/interfaces/Settings/interface.ilManualAssessmentSettingsStorage.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettings.php';
require_once 'Modules/ManualAssessment/classes/class.ilObjManualAssessment.php';
class ilManualAssessmentSettingsStorageDB implements ilManualAssessmentSettingsStorage {

	protected $db;
	public function __construct($db) {
		$this->db = $db;
	}

	public function createSettings(ilManualAssessmentSettings $settings) {
		$sql = "INSERT INTO mass_settings (content,record_template,obj_id) VALUES (%s,%s,%s)";
		$this->db->manipulateF($sql,array("text","text","integer"),array($settings->content(),$settings->recordTemplate(),$settings->getId()));
	}

	public function loadSettings(ilObjManualAssessment $obj) {
		$obj_id = $obj->getId();
		assert('is_numeric($obj_id)');
		$sql = "SELECT content, record_template FROM mass_settings WHERE obj_id = ".$this->db->quote($obj_id,'integer');
		if($res = $this->db->fetchAssoc($this->db->query($sql))) {
			return new ilManualAssessmentSettings($obj, $res["content"],$res["record_template"]);
		}
		throw new ilManualAssessmentException("no $obj_id");
	}
	
	public function updateSettings(ilManualAssessmentSettings $settings) {
		$sql = "UPDATE mass_settings SET content = %s,record_template = %s WHERE obj_id = %s";
		$this->db->manipulateF($sql,array("text","text","integer"),array($settings->content(),$settings->recordTemplate(),$settings->getId()));
	}

	public function deleteSettings(ilObjManualAssessment $obj) {
		$sql = "DELETE FROM mass_settings WHERE obj_id = %s";
		$this->db->manipulateF($sql,array("integer"),array($obj->getId()));
	}
}