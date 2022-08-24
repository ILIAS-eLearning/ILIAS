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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseParticipant extends ilParticipant
{
    protected const COMPONENT_NAME = 'Modules/Course';

    protected static array $instances = [];

    /**
     * @todo get rid of these pseudo constants
     */
    protected function __construct(int $a_obj_id, int $a_usr_id)
    {
        $this->type = 'crs';

        parent::__construct(self::COMPONENT_NAME, $a_obj_id, $a_usr_id);
    }

    public static function _getInstanceByObjId(int $a_obj_id, int $a_usr_id): ilCourseParticipant
    {
        if (isset(self::$instances[$a_obj_id][$a_usr_id]) && self::$instances[$a_obj_id][$a_usr_id]) {
            return self::$instances[$a_obj_id][$a_usr_id];
        }
        return self::$instances[$a_obj_id][$a_usr_id] = new ilCourseParticipant($a_obj_id, $a_usr_id);
    }
}
