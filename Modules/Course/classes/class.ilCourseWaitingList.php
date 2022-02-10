<?php declare(strict_types=0);

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course waiting list
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @extends ilWaitingList
 */
class ilCourseWaitingList extends ilWaitingList
{
    private ilLogger $logger;

    public function __construct(int $a_obj_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
        parent::__construct($a_obj_id);
    }

    public function addToList(int $a_usr_id) : bool
    {
        if (!parent::addToList($a_usr_id)) {
            return false;
        }
        $this->logger->debug('Raise new event: Modules/Course addToList');
        $this->eventHandler->raise(
            "Modules/Course",
            'addToWaitingList',
            [
                'obj_id' => $this->getObjId(),
                'usr_id' => $a_usr_id
            ]
        );
        return true;
    }

    public function removeFromList(int $a_usr_id) : bool
    {
        if (!parent::removeFromList($a_usr_id)) {
            return false;
        }
        $this->logger->debug('Raise new event: Modules/Course removeFromList');
        $this->eventHandler->raise(
            "Modules/Course",
            'removeFromWaitingList',
            [
                'obj_id' => $this->getObjId(),
                'usr_id' => $a_usr_id
            ]
        );
        return true;
    }
}
