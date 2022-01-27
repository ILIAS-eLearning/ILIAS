<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
 * Course waiting list
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @extends ilWaitingList
 */
class ilCourseWaitingList extends ilWaitingList
{
    /**
     * Add to waiting list and raise event
     * @param int $a_usr_id
     */
    public function addToList(int $a_usr_id) : bool
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $ilLog = $DIC['ilLog'];
        
        if (!parent::addToList($a_usr_id)) {
            return false;
        }
        
        $ilLog->write(__METHOD__ . ': Raise new event: Modules/Course addToList');
        $ilAppEventHandler->raise(
            "Modules/Course",
            'addToWaitingList',
            array(
                    'obj_id' => $this->getObjId(),
                    'usr_id' => $a_usr_id
                )
        );
        return true;
    }


    /**
     * Remove from waiting list and raise event
     * @param int $a_usr_id
     */
    public function removeFromList(int $a_usr_id) : void
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $ilLog = $DIC['ilLog'];

        if (!parent::removeFromList($a_usr_id)) {
            return;
        }

        $ilLog->write(__METHOD__ . ': Raise new event: Modules/Course removeFromList');
        $ilAppEventHandler->raise(
            "Modules/Course",
            'removeFromWaitingList',
            array(
                'obj_id' => $this->getObjId(),
                'usr_id' => $a_usr_id
            )
        );
    }
}
