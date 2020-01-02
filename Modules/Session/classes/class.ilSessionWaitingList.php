<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Membership/classes/class.ilWaitingList.php');

/**
 * Session waiting list
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @extends ilWaitingList
 */
class ilSessionWaitingList extends ilWaitingList
{
    
    /**
     * Add to waiting list and raise event
     * @param int $a_usr_id
     */
    public function addToList($a_usr_id)
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $ilLog = $DIC->logger()->sess();
        
        if (!parent::addToList($a_usr_id)) {
            return false;
        }
        
        $ilLog->info('Raise new event: Modules/Session addToWaitingList');
        $ilAppEventHandler->raise(
            "Modules/Session",
            'addToWaitingList',
            array(
                    'obj_id' => $this->getObjId(),
                    'usr_id' => $a_usr_id
                )
        );
        return true;
    }
}
