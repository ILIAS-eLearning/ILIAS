<?php

declare(strict_types=0);
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
 * Caches results for a specific user and course
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesCourse
 */
class ilCourseObjectiveResultCache
{
    private static array $suggested = [];
    private static array $status = [];

    public static function isSuggested(int $a_usr_id, int $a_crs_id, int $a_objective_id): bool
    {
        if (!is_array(self::$suggested[$a_usr_id][$a_crs_id])) {
            self::$suggested[$a_usr_id][$a_crs_id] = self::readSuggested($a_usr_id, $a_crs_id);
        }
        return in_array($a_objective_id, self::$suggested[$a_usr_id][$a_crs_id]);
    }

    public static function getStatus(int $a_usr_id, int $a_crs_id): string
    {
        if (isset(self::$status[$a_usr_id][$a_crs_id])) {
            return self::$status[$a_usr_id][$a_crs_id];
        }
        $tmp_res = new ilCourseObjectiveResult($a_usr_id);
        return self::$status[$a_usr_id][$a_crs_id] = $tmp_res->getStatus($a_crs_id);
    }

    protected function readSuggested(int $a_usr_id, int $a_crs_id): array
    {
        return ilCourseObjectiveResult::_getSuggested($a_usr_id, $a_crs_id, self::getStatus($a_usr_id, $a_crs_id));
    }
}
