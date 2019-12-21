<?php

require_once 'Modules/IndividualAssessment/interfaces/Settings/interface.ilIndividualAssessmentSettingsStorage.php';
require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentSettings.php';
require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentInfoSettings.php';
require_once 'Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php';

/**
 * A settings storage handler to write iass settings to db.
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilIndividualAssessmentSettingsStorageDB implements ilIndividualAssessmentSettingsStorage
{
    const IASS_SETTINGS_TABLE = "iass_settings";
    const IASS_SETTINGS_INFO_TABLE = "iass_info_settings";

    protected $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function createSettings(ilIndividualAssessmentSettings $settings)
    {
        $values = array( "obj_id" => array("integer", $settings->getId())
                , "content" => array("text", $settings->content())
                , "record_template" => array("text", $settings->recordTemplate())
                , "event_time_place_required" => array("integer", $settings->eventTimePlaceRequired())
                , "file_required" => array("integer", $settings->fileRequired())
                );

        $this->db->insert(self::IASS_SETTINGS_TABLE, $values);

        $values = array("obj_id" => array("integer", $settings->getId()));
        $this->db->insert(self::IASS_SETTINGS_INFO_TABLE, $values);
    }

    /**
     * @inheritdoc
     */
    public function loadSettings(ilObjIndividualAssessment $obj)
    {
        if (ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
            $obj_id = $obj->getId();
            assert(is_numeric($obj_id));
            $sql = "SELECT content, record_template, event_time_place_required, file_required\n"
                  . " FROM " . self::IASS_SETTINGS_TABLE . "\n"
                  . " WHERE obj_id = " . $this->db->quote($obj_id, 'integer');

            if ($res = $this->db->fetchAssoc($this->db->query($sql))) {
                return new ilIndividualAssessmentSettings(
                    $obj,
                    $res["content"],
                    $res["record_template"],
                    (bool) $res["event_time_place_required"],
                    (bool) $res['file_required']
                );
            }
            throw new ilIndividualAssessmentException("$obj_id not in database");
        } else {
            return new ilIndividualAssessmentSettings($obj);
        }
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(ilIndividualAssessmentSettings $settings)
    {
        $where = array( "obj_id" => array("integer", $settings->getId()));

        $values = array( "content" => array("text", $settings->content())
                , "record_template" => array("text", $settings->recordTemplate())
                , "event_time_place_required" => array("integer", $settings->eventTimePlaceRequired())
                , "file_required" => array("integer", $settings->fileRequired())
                );

        $this->db->update(self::IASS_SETTINGS_TABLE, $values, $where);
    }

    /**
     * Load info-screen settings corresponding to obj
     *
     * @param	ilObjIndividualAssessment	$obj
     * @return	ilIndividualAssessmentSettings	$settings
     */
    public function loadInfoSettings(ilObjIndividualAssessment $obj)
    {
        if (ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
            $obj_id = $obj->getId();
            assert(is_numeric($obj_id));
            $sql = "SELECT contact, responsibility, phone, mails, consultation_hours"
                    . " FROM " . self::IASS_SETTINGS_INFO_TABLE . " WHERE obj_id = " . $this->db->quote($obj_id, 'integer');

            if ($res = $this->db->fetchAssoc($this->db->query($sql))) {
                return new ilIndividualAssessmentInfoSettings(
                    $obj,
                    $res["contact"],
                    $res["responsibility"],
                    $res['phone'],
                    $res['mails'],
                    $res['consultation_hours']
                );
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
    public function updateInfoSettings(ilIndividualAssessmentInfoSettings $settings)
    {
        $where = array("obj_id" => array("integer", $settings->id()));

        $values = array( "contact" => array("text", $settings->contact())
                , "responsibility" => array("text", $settings->responsibility())
                , "phone" => array("text", $settings->phone())
                , "mails" => array("text", $settings->mails())
                , "consultation_hours" => array("text", $settings->consultationHours())
                );

        $this->db->update(self::IASS_SETTINGS_INFO_TABLE, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteSettings(ilObjIndividualAssessment $obj)
    {
        $sql = "DELETE FROM " . self::IASS_SETTINGS_TABLE . " WHERE obj_id = %s";
        $this->db->manipulateF($sql, array("integer"), array($obj->getId()));

        $sql = "DELETE FROM " . self::IASS_SETTINGS_INFO_TABLE . " WHERE obj_id = %s";
        $this->db->manipulateF($sql, array("integer"), array($obj->getId()));
    }
}
