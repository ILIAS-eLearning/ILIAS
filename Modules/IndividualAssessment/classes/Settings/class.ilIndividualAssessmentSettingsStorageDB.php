<?php

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
        $values = array( "obj_id" => array("integer", $settings->getObjId())
                , "content" => array("text", $settings->getContent())
                , "record_template" => array("text", $settings->getRecordTemplate())
                , "event_time_place_required" => array("integer", $settings->isEventTimePlaceRequired())
                , "file_required" => array("integer", $settings->isFileRequired())
                );

        $this->db->insert(self::IASS_SETTINGS_TABLE, $values);

        $values = array("obj_id" => array("integer", $settings->getObjId()));
        $this->db->insert(self::IASS_SETTINGS_INFO_TABLE, $values);
    }

    /**
     * @inheritdoc
     */
    public function loadSettings(ilObjIndividualAssessment $obj) : \ilIndividualAssessmentSettings
    {
        if (ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
            $obj_id = $obj->getId();
            assert(is_numeric($obj_id));
            $sql = "SELECT content, record_template, event_time_place_required, file_required\n"
                  . " FROM " . self::IASS_SETTINGS_TABLE . "\n"
                  . " WHERE obj_id = " . $this->db->quote($obj_id, 'integer');

            if ($res = $this->db->fetchAssoc($this->db->query($sql))) {
                return new ilIndividualAssessmentSettings(
                    $obj->getId(),
                    $obj->getTitle(),
                    $obj->getDescription(),
                    $res["content"],
                    $res["record_template"],
                    (bool) $res["event_time_place_required"],
                    (bool) $res['file_required']
                );
            }
            throw new ilIndividualAssessmentException("$obj_id not in database");
        } else {
            return new ilIndividualAssessmentSettings(
                (int) $obj->getId(),
                '',
                '',
                '',
                '',
                false,
                false
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(ilIndividualAssessmentSettings $settings)
    {
        $where = array( "obj_id" => array("integer", $settings->getObjId()));

        $values = array( "content" => array("text", $settings->getContent())
                , "record_template" => array("text", $settings->getRecordTemplate())
                , "event_time_place_required" => array("integer", $settings->isEventTimePlaceRequired())
                , "file_required" => array("integer", $settings->isFileRequired())
                );

        $this->db->update(self::IASS_SETTINGS_TABLE, $values, $where);
    }

    /**
     * Load info-screen settings corresponding to obj
     */
    public function loadInfoSettings(ilObjIndividualAssessment $obj) : \ilIndividualAssessmentInfoSettings
    {
        if (ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
            $obj_id = $obj->getId();
            assert(is_numeric($obj_id));
            $sql = "SELECT contact, responsibility, phone, mails, consultation_hours"
                    . " FROM " . self::IASS_SETTINGS_INFO_TABLE . " WHERE obj_id = " . $this->db->quote($obj_id, 'integer');

            if ($res = $this->db->fetchAssoc($this->db->query($sql))) {
                return new ilIndividualAssessmentInfoSettings(
                    (int) $obj->getId(),
                    $res["contact"],
                    $res["responsibility"],
                    $res['phone'],
                    $res['mails'],
                    $res['consultation_hours']
                );
            }
            throw new ilIndividualAssessmentException("$obj_id not in database");
        } else {
            return new ilIndividualAssessmentInfoSettings((int) $obj->getId());
        }
    }

    /**
     * Update info-screen settings entry.
     *
     * @param	ilIndividualAssessmentSettings	$settings
     */
    public function updateInfoSettings(ilIndividualAssessmentInfoSettings $settings)
    {
        $where = array("obj_id" => array("integer", $settings->getObjId()));

        $values = array( "contact" => array("text", $settings->getContact())
                , "responsibility" => array("text", $settings->getResponsibility())
                , "phone" => array("text", $settings->getPhone())
                , "mails" => array("text", $settings->getMails())
                , "consultation_hours" => array("text", $settings->getConsultationHours())
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
