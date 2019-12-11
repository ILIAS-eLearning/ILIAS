<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Survey\Settings;

/**
 * Survey settings db repository.
 *
 * This should wrap all svy_svy calls in the future.
 *
 * @author killing@leifos.de
 */
class SettingsDBRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var SettingsFactory
     */
    protected $set_factory;

    /**
     * Constructor
     */
    public function __construct(\ilDBInterface $db = null, SettingsFactory $set_factory = null)
    {
        global $DIC;

        $this->db = (is_null($db))
            ? $DIC->database()
            : $db;

        $this->set_factory = (is_null($set_factory))
            ? new SettingsFactory()
            : $set_factory;
    }

    /**
     * Check if surveys have ended
     *
     * @param int[] $survey_ids survey IDs
     * @return bool[] has ended true/false
     */
    public function hasEnded(array $survey_ids) : array
    {
        $db = $this->db;
        
        $set = $db->queryF(
            "SELECT survey_id, enddate FROM svy_svy " .
            " WHERE " . $db->in("survey_id", $survey_ids, false, "integer"),
            [],
            []
        );
        $has_ended = [];
        while ($rec = $db->fetchAssoc($set)) {
            $has_ended[(int) $rec["survey_id"]] = !($rec["enddate"] == 0 || $this->toUnixTS($rec["enddate"]) > time());
        }
        return $has_ended;
    }

    /**
     * Check if surveys have ended
     *
     * @param int[] $survey_ids survey IDs
     * @return bool[] has ended true/false
     */
    public function getObjIdsForSurveyIds(array $survey_ids) : array
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT survey_id, obj_fi FROM svy_svy " .
            " WHERE " . $db->in("survey_id", $survey_ids, false, "integer"),
            [],
            []
        );
        $obj_ids = [];
        while ($rec = $db->fetchAssoc($set)) {
            $obj_ids[(int) $rec["survey_id"]] = (int) $rec["obj_fi"];
        }
        return $obj_ids;
    }

    /**
     * Unix time from survey date
     *
     * @param string
     * @return int
     */
    protected function toUnixTS($date) : int
    {
        if ($date > 0) {
            if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $date, $matches)) {
                return (int) mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
            }
        }
        return 0;
    }

    /**
     * Get access settings
     *
     * @param int[] $survey_ids
     * @return AccessSettings[]
     */
    public function getAccessSettings(array $survey_ids) : array
    {
        $db = $this->db;
        
        $set = $db->queryF(
            "SELECT startdate, enddate, anonymize, survey_id FROM svy_svy " .
            " WHERE " . $db->in("survey_id", $survey_ids, false, "integer"),
            [],
            []
        );
        $settings = [];
        while ($rec = $db->fetchAssoc($set)) {
            $settings[(int) $rec["survey_id"]] = $this->set_factory->accessSettings(
                $this->toUnixTS($rec["startdate"]),
                $this->toUnixTS($rec["enddate"]),
                in_array($rec["anonymize"], ["1", "3"])
            );
        }
        return $settings;
    }
}
