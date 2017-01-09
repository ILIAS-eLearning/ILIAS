<?php
/** 
 * A settings storage handler to write iass settings to db.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de> 
 */
require_once 'Modules/IndividualAssessment/interfaces/Settings/interface.ilIndividualAssessmentSettingsStorage.php';
require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentSettings.php';
require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentInfoSettings.php';
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';
class ilIndividualAssessmentSettingsStorageDB implements ilIndividualAssessmentSettingsStorage {

	protected $db;
	public function __construct($db) {
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createSettings(ilIndividualAssessmentSettings $settings) {
		$sql = "INSERT INTO iass_settings (content,record_template,obj_id) VALUES (%s,%s,%s)";
		$this->db->manipulateF($sql,array("text","text","integer"),array($settings->content(),$settings->recordTemplate(),$settings->getId()));
		$sql = "INSERT INTO iass_info_settings (obj_id) VALUES (%s)";
		$this->db->manipulateF($sql,array("integer"),array($settings->getId()));
	}

	/**
	 * @inheritdoc
	 */
	public function loadSettings(ilObjIndividualAssessment $obj) {
		if(ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
			$obj_id = $obj->getId();
			assert('is_numeric($obj_id)');
			$sql = 'SELECT content, record_template FROM iass_settings WHERE obj_id = '.$this->db->quote($obj_id,'integer');
			if($res = $this->db->fetchAssoc($this->db->query($sql))) {
				return new ilIndividualAssessmentSettings($obj, $res["content"],$res["record_template"]);
			}
			throw new ilIndividualAssessmentException("$obj_id not in database");
		} else {
			return new ilIndividualAssessmentSettings($obj, ilIndividualAssessmentSettings::DEF_CONTENT, ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function updateSettings(ilIndividualAssessmentSettings $settings) {
		$sql = 'UPDATE iass_settings SET content = %s,record_template = %s WHERE obj_id = %s';
		$this->db->manipulateF($sql,array("text","text","integer"),array($settings->content(),$settings->recordTemplate(),$settings->getId()));
	}


	/**
	 * Load info-screen settings corresponding to obj
	 *
	 * @param	ilObjIndividualAssessment	$obj
	 * @return	ilIndividualAssessmentSettings	$settings
	 */
	public function loadInfoSettings(ilObjIndividualAssessment $obj) {
		if(ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
			$obj_id = $obj->getId();
			assert('is_numeric($obj_id)');
			$sql = 	'SELECT contact, responsibility, phone, mails, consultation_hours'
					.'	FROM iass_info_settings WHERE obj_id = '.$this->db->quote($obj_id,'integer');
			if($res = $this->db->fetchAssoc($this->db->query($sql))) {
				return new ilIndividualAssessmentInfoSettings($obj,
					$res["contact"]
					,$res["responsibility"]
					,$res['phone']
					,$res['mails']
					,$res['consultation_hours']);
			}
			throw new ilIndividualAssessmentException("$obj_id not in database");
		} else {
			return new ilIndividualAssessmentInfoSettings($obj);
		}
	}

	/**
	 * Update info-screen settings entry.
	 *
	 * @param	ilIndividualAssessmentSettings	$settings
	 */
	public function updateInfoSettings(ilIndividualAssessmentInfoSettings $settings) {
		$sql = 	'UPDATE iass_info_settings SET '
				.'	contact = %s'
				.'	,responsibility = %s'
				.'	,phone = %s'
				.'	,mails = %s'
				.'	,consultation_hours = %s'
				.' WHERE obj_id = %s';
		$this->db->manipulateF($sql,array('text','text','text','text','text','integer'),
			array(	$settings->contact()
					,$settings->responsibility()
					,$settings->phone()
					,$settings->mails()
					,$settings->consultationHours()
					,$settings->id()));
	}

	/**
	 * @inheritdoc
	 */
	public function deleteSettings(ilObjIndividualAssessment $obj) {
		$sql = 'DELETE FROM iass_settings WHERE obj_id = %s';
		$this->db->manipulateF($sql,array("integer"),array($obj->getId()));
		$sql = 'DELETE FROM iass_info_settings WHERE obj_id = %s';
		$this->db->manipulateF($sql,array("integer"),array($obj->getId()));
	}
}