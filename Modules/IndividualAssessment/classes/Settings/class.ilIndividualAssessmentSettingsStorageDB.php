<?php

declare(strict_types=1);

/* Copyright (c) 2018 - Denis Klöpfer <denis.kloepfer@concepts-and-training.de> - Extended GPL, see LICENSE */
/* Copyright (c) 2018 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * A settings storage handler to write iass settings to db.
 */
class ilIndividualAssessmentSettingsStorageDB implements ilIndividualAssessmentSettingsStorage
{
    public const IASS_SETTINGS_TABLE = "iass_settings";
    public const IASS_SETTINGS_INFO_TABLE = "iass_info_settings";

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function createSettings(ilIndividualAssessmentSettings $settings): void
    {
        $values = [
            "obj_id" => ["integer", $settings->getObjId()],
            "content" => ["text", $settings->getContent()],
            "record_template" => ["text", $settings->getRecordTemplate()],
            "event_time_place_required" => ["integer", $settings->isEventTimePlaceRequired()],
            "file_required" => ["integer", $settings->isFileRequired()]
        ];

        $this->db->insert(self::IASS_SETTINGS_TABLE, $values);

        $values = ["obj_id" => ["integer", $settings->getObjId()]];
        $this->db->insert(self::IASS_SETTINGS_INFO_TABLE, $values);
    }

    /**
     * @inheritdoc
     */
    public function loadSettings(ilObjIndividualAssessment $obj): ilIndividualAssessmentSettings
    {
        if (!ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
            return new ilIndividualAssessmentSettings(
                $obj->getId(),
                '',
                '',
                '',
                '',
                false,
                false
            );
        }

        $sql =
             "SELECT content, record_template, event_time_place_required, file_required" . PHP_EOL
            . "FROM " . self::IASS_SETTINGS_TABLE . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($obj->getId(), 'integer') . PHP_EOL
        ;

        $result = $this->db->query($sql);

        if ($this->db->numRows($result) == 0) {
            throw new ilIndividualAssessmentException($obj->getId() . " not in database");
        }

        $row = $this->db->fetchAssoc($result);

        return new ilIndividualAssessmentSettings(
            $obj->getId(),
            $obj->getTitle(),
            $obj->getDescription(),
            $row["content"],
            $row["record_template"],
            (bool) $row["event_time_place_required"],
            (bool) $row['file_required']
        );
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(ilIndividualAssessmentSettings $settings): void
    {
        $where = ["obj_id" => ["integer", $settings->getObjId()]];

        $values = [
            "content" => ["text", $settings->getContent()],
            "record_template" => ["text", $settings->getRecordTemplate()],
            "event_time_place_required" => ["integer", $settings->isEventTimePlaceRequired()],
            "file_required" => ["integer", $settings->isFileRequired()]
        ];

        $this->db->update(self::IASS_SETTINGS_TABLE, $values, $where);
    }

    /**
     * Load info-screen settings corresponding to obj
     */
    public function loadInfoSettings(ilObjIndividualAssessment $obj): ilIndividualAssessmentInfoSettings
    {
        if (!ilObjIndividualAssessment::_exists($obj->getId(), false, 'iass')) {
            return new ilIndividualAssessmentInfoSettings($obj->getId());
        }

        $sql =
            "SELECT contact, responsibility, phone, mails, consultation_hours" . PHP_EOL
            . "FROM " . self::IASS_SETTINGS_INFO_TABLE . PHP_EOL
            . "WHERE obj_id = " . $this->db->quote($obj->getId(), 'integer') . PHP_EOL
        ;

        $result = $this->db->query($sql);

        if ($this->db->numRows($result) == 0) {
            throw new ilIndividualAssessmentException($obj->getId() . " not in database");
        }

        $row = $this->db->fetchAssoc($result);

        return new ilIndividualAssessmentInfoSettings(
            $obj->getId(),
            $row["contact"],
            $row["responsibility"],
            $row['phone'],
            $row['mails'],
            $row['consultation_hours']
        );
    }

    /**
     * Update info-screen settings entry.
     */
    public function updateInfoSettings(ilIndividualAssessmentInfoSettings $settings): void
    {
        $where = ["obj_id" => ["integer", $settings->getObjId()]];

        $values = [
            "contact" => ["text", $settings->getContact()],
            "responsibility" => ["text", $settings->getResponsibility()],
            "phone" => ["text", $settings->getPhone()],
            "mails" => ["text", $settings->getMails()],
            "consultation_hours" => ["text", $settings->getConsultationHours()]
        ];

        $this->db->update(self::IASS_SETTINGS_INFO_TABLE, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function deleteSettings(ilObjIndividualAssessment $obj): void
    {
        $sql = "DELETE FROM " . self::IASS_SETTINGS_TABLE . " WHERE obj_id = %s";
        $this->db->manipulateF($sql, array("integer"), array($obj->getId()));

        $sql = "DELETE FROM " . self::IASS_SETTINGS_INFO_TABLE . " WHERE obj_id = %s";
        $this->db->manipulateF($sql, array("integer"), array($obj->getId()));
    }
}
