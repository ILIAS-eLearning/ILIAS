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

/**
 * Class ilLTIConsumerGradeSynchronization
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 *
 * @package     Modules/LTIConsumer
 */

class ilLTIConsumerGradeSynchronization
{
    public int $id;
    public int $obj_id;
    public int $usr_id;

    /**
     * @var float|null
     */
    public ?float $result = null;


    public function getId(): int
    {
        return $this->id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function getResult(): ?float
    {
        return $this->result;
    }

    public static function getGradesForObject(int $objId, ?int $usrID = null, ?string $activity_progress = null, ?string $grading_progress = null, ?ilDateTime $startDate = null, ?ilDateTime $endDate = null): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = 'SELECT * FROM lti_consumer_grades'
            . ' WHERE obj_id = ' . $DIC->database()->quote($objId, 'integer');

        if ($usrID != null) {
            $query .= ' AND usr_id = ' . $DIC->database()->quote($usrID, 'integer');
        }

        if ($activity_progress != null) {
            $query .= ' AND activity_progress = ' . $DIC->database()->quote($activity_progress, 'text');
        }

        if ($grading_progress != null) {
            $query .= ' AND grading_progress = ' . $DIC->database()->quote($grading_progress, 'text');
        }

        if ($startDate != null && $startDate->get(IL_CAL_DATETIME) != null) {
            $query .= ' AND lti_timestamp >= ' . $DIC->database()->quote($startDate->get(IL_CAL_DATETIME), 'timestamp');
        }

        if ($endDate != null && $endDate->get(IL_CAL_DATETIME) != null) {
            $query .= ' AND lti_timestamp <= ' . $DIC->database()->quote($endDate->get(IL_CAL_DATETIME), 'timestamp');
        }

        $query .= ' ORDER BY lti_timestamp DESC';

        $res = $DIC->database()->query($query);

        $results = [];

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $results[] = $row;
        }

        return $results;
    }
}
