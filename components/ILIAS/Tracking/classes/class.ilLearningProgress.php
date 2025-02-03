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

/**
 * Class ilLearningProgress
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @package ilias-core
 */
class ilLearningProgress
{
    // Static
    public static function _tracProgress(
        int $a_user_id,
        int $a_obj_id,
        int $a_ref_id,
        string $a_obj_type = ''
    ): bool {
        ilChangeEvent::_recordReadEvent(
            $a_obj_type,
            $a_ref_id,
            $a_obj_id,
            $a_user_id
        );

        ilLPStatus::setInProgressIfNotAttempted($a_obj_id, $a_user_id);

        return true;
    }

    public static function _getProgress(int $a_user_id, int $a_obj_id): array
    {
        $events = ilChangeEvent::_lookupReadEvents($a_obj_id, $a_user_id);

        $progress = [];
        foreach ($events as $row) {
            $tmp_date = new ilDateTime($row['last_access'], IL_CAL_UNIX);
            $row['last_access'] = $tmp_date->get(IL_CAL_UNIX);

            $tmp_date = new ilDateTime($row['first_access'], IL_CAL_DATETIME);
            $row['first_access'] = $tmp_date->get(IL_CAL_UNIX);

            if ($progress) {
                $progress['spent_seconds'] += (int) $row['spent_seconds'];
                $progress['access_time'] = max(
                    $progress['access_time'],
                    (int) $row['last_access']
                );
                $progress['access_time_min'] = min(
                    $progress['access_time_min'],
                    (int) $row['first_access']
                );
            } else {
                $progress['obj_id'] = (int) $row['obj_id'];
                $progress['user_id'] = (int) $row['usr_id'];
                $progress['spent_seconds'] = (int) $row['spent_seconds'];
                $progress['access_time'] = (int) $row['last_access'];
                $progress['access_time_min'] = (int) $row['first_access'];
                $progress['visits'] = (int) $row['read_count'];
            }
        }
        return $progress;
    }

    /**
     * lookup progress for a specific object
     */
    public static function _lookupProgressByObjId(int $a_obj_id): array
    {
        $progress = [];
        foreach (ilChangeEvent::_lookupReadEvents($a_obj_id) as $row) {
            if (isset($progress[$row['usr_id']])) {
                $progress[$row['usr_id']]['spent_seconds'] += (int) $row['spent_seconds'];
                $progress[$row['usr_id']]['read_count'] += (int) $row['read_count'];
                $progress[$row['usr_id']]['ts'] = max(
                    (int) $row['last_access'],
                    (int) $progress[$row['usr_id']]['ts']
                );
            } else {
                $progress[$row['usr_id']]['spent_seconds'] = (int) $row['spent_seconds'];
                $progress[$row['usr_id']]['read_count'] = (int) $row['read_count'];
                $progress[$row['usr_id']]['ts'] = (int) $row['last_access'];
            }
            $progress[$row['usr_id']]['usr_id'] = (int) $row['usr_id'];
            $progress[$row['usr_id']]['obj_id'] = (int) $row['obj_id'];
        }
        return $progress;
    }
}
