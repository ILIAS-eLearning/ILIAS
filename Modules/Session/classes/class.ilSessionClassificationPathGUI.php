<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * PathGUI which handles materials assigned to sessions

 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesSession
 */
class ilSessionClassificationPathGUI extends ilPathGUI
{

    /**
     * @param $a_obj_id
     * @return string|void
     */
    protected function buildTitle($a_obj_id)
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
    protected function getPathIds()
    {
        global $DIC;

        $access = $DIC->access();

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

    /**
     * @param int $item_ref_id
     */
    protected function findSessionContainerForItem($item_ref_id)
    {
        global $DIC;

        $access = $DIC->access();

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
                return $session_ref_id;
            }
        }
        return 0;
    }
}
