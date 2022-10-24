<?php

declare(strict_types=1);

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
 ********************************************************************
 */

/**
 * PathGUI which handles materials assigned to sessions

 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesSession
 */
class ilSessionClassificationPathGUI extends ilPathGUI
{
    protected ilAccess $access;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->access = $DIC->access();
    }

    protected function buildTitle(int $a_obj_id): string
    {
        if (ilObject::_lookupType($a_obj_id) !== 'sess') {
            return ilObject::_lookupTitle($a_obj_id);
        }
        $sess = new \ilObjSession($a_obj_id, false);
        return $sess->getPresentationTitleAppointmentPeriod();
    }

    /**
     * @inheritdoc
     */
    protected function getPathIds(): array
    {
        $this->enableHideLeaf(false);
        $path = parent::getPathIds();
        $this->enableHideLeaf(true);

        $new_path = [];
        foreach ($path as $path_item_ref_id) {
            $session_container = $this->findSessionContainerForItem($path_item_ref_id);
            if ($session_container) {
                $new_path[] = $session_container;
            }
            $new_path[] = $path_item_ref_id;
        }

        // hide leaf
        if (is_array($new_path) && count($new_path) > 0) {
            unset($new_path[count($new_path) - 1]);
        }
        return $new_path;
    }

    protected function findSessionContainerForItem(int $item_ref_id): int
    {
        $access = $this->access;

        $accessible = [];
        foreach (ilEventItems::getEventsForItemOrderedByStartingTime($item_ref_id) as $event_obj_id => $unix_time) {
            foreach (ilObject::_getAllReferences($event_obj_id) as $something => $session_ref_id) {
                if ($access->checkAccess('read', '', $session_ref_id)) {
                    $accessible[$session_ref_id] = $unix_time;
                    break;
                }
            }
        }


        // find closest in the future
        $now = new ilDate(time(), IL_CAL_UNIX);
        $now->increment(IL_CAL_DAY, -1);
        $last = 0;
        foreach ($accessible as $session_ref_id => $unix_time) {
            $session_date = new ilDate($unix_time, IL_CAL_UNIX);
            $last = $session_ref_id;

            if (
                ilDate::_equals($now, $session_date, IL_CAL_DAY) ||
                ilDate::_after($session_date, $now, IL_CAL_DAY)
            ) {
                return (int) $session_ref_id;
            }
        }
        return 0;
    }
}
