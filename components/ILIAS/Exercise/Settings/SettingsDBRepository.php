<?php

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

declare(strict_types=1);

namespace ILIAS\Exercise\Settings;

use ilDBInterface;
use ILIAS\Exercise\InternalDataService;

class SettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    public function create(Settings $settings): void
    {
        $this->db->insert("exc_data", [
            "obj_id" => ["integer", $settings->getObjId()],
            "instruction" => ["clob", $settings->getInstruction()],
            "time_stamp" => ["integer", $settings->getTimeStamp()],
            "pass_mode" => ["text", $settings->getPassMode()],
            "nr_mandatory_random" => ["integer", $settings->getNrMandatoryRandom()],
            "pass_nr" => ["text", $settings->getPassNr()],
            "show_submissions" => ["integer", (int) $settings->getShowSubmissions()],
            'compl_by_submission' => ["integer", (int) $settings->getCompletionBySubmission()],
            "certificate_visibility" => ["integer", $settings->getCertificateVisibility()],
            "tfeedback" => ["integer", $settings->getTutorFeedback()]
        ]);
    }

    public function update(Settings $settings): void
    {
        $this->db->update("exc_data", [
            "instruction" => ["clob", $settings->getInstruction()],
            "time_stamp" => ["integer", $settings->getTimeStamp()],
            "pass_mode" => ["text", $settings->getPassMode()],
            "nr_mandatory_random" => ["integer", $settings->getNrMandatoryRandom()],
            "pass_nr" => ["text", $settings->getPassNr()],
            "show_submissions" => ["integer", (int) $settings->getShowSubmissions()],
            'compl_by_submission' => ["integer", (int) $settings->getCompletionBySubmission()],
            "certificate_visibility" => ["integer", $settings->getCertificateVisibility()],
            "tfeedback" => ["integer", $settings->getTutorFeedback()]
        ], [
            "obj_id" => ["integer", $settings->getObjId()]
        ]);
    }

    public function getByObjId(int $obj_id): ?Settings
    {
        $set = $this->db->queryF(
            "SELECT * FROM exc_data WHERE obj_id = %s",
            ["integer"],
            [$obj_id]
        );

        $rec = $this->db->fetchAssoc($set);
        if ($rec !== false) {
            return $this->getSettingsFromRecord($rec);
        }

        return null;
    }

    public function delete(int $obj_id): void
    {
        $this->db->manipulateF(
            "DELETE FROM exc_data WHERE obj_id = %s",
            ["integer"],
            [$obj_id]
        );
    }

    protected function getSettingsFromRecord(array $rec): Settings
    {
        return $this->data->settings(
            (int) $rec['obj_id'],
            $rec['instruction'],
            (int) $rec['time_stamp'],
            $rec['pass_mode'],
            (int) $rec['nr_mandatory_random'],
            (int) $rec['pass_nr'],
            (bool) $rec['show_submissions'],
            (bool) $rec['compl_by_submission'],
            (int) $rec['certificate_visibility'],
            (int) $rec['tfeedback']
        );
    }
}
