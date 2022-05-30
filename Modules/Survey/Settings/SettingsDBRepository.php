<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Survey\Settings;

use ILIAS\Survey\InternalDataService;

/**
 * Survey settings db repository.
 * This should wrap all svy_svy calls in the future.
 * @author Alexander Killing <killing@leifos.de>
 */
class SettingsDBRepository
{
    protected \ilDBInterface $db;
    protected SettingsFactory $set_factory;

    public function __construct(
        InternalDataService $data,
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->set_factory = $data->settings();
    }

    /**
     * Check if surveys have ended
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
            $has_ended[(int) $rec["survey_id"]] = !((int) $rec["enddate"] === 0 || $this->toUnixTS($rec["enddate"]) > time());
        }
        return $has_ended;
    }

    /**
     * @param int[] $survey_ids
     * @return array<int,int>
     */
    public function getObjIdsForSurveyIds(
        array $survey_ids
    ) : array {
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
     * @param string
     * @return int
     */
    protected function toUnixTS(
        string $date
    ) : int {
        if ($date > 0 && preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $date, $matches)) {
            return (int) mktime((int) $matches[4], (int) $matches[5], (int) $matches[6], (int) $matches[2], (int) $matches[3], (int) $matches[1]);
        }
        return 0;
    }

    /**
     * @param int[] $survey_ids
     * @return AccessSettings[]
     */
    public function getAccessSettings(
        array $survey_ids
    ) : array {
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
                $this->toUnixTS($rec["startdate"] ?? ''),
                $this->toUnixTS($rec["enddate"] ?? ''),
                in_array($rec["anonymize"], ["1", "3"], true)
            );
        }
        return $settings;
    }
}
