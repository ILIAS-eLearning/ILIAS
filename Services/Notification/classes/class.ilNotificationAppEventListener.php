<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNotificationAppEventListener implements ilAppEventListener
{
    public static function handleEvent(
        string $a_component,
        string $a_event,
        array $a_parameter
    ): void {
        if ($a_component === 'Services/Object' && $a_event === 'delete') {
            if ($a_parameter['obj_id'] > 0) {
                $set = new ilObjNotificationSettings($a_parameter['obj_id']);
                $set->delete();
            }
        }
    }
}
