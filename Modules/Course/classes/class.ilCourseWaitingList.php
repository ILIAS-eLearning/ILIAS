<?php declare(strict_types=0);

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
