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
 *********************************************************************/

class ilLearningSequenceWaitingList extends ilWaitingList
{
    public function addToList(int $a_usr_id): bool
    {
        global $DIC;

        $app_event_handler = $DIC->event();
        $log = $DIC->logger();

        if (!parent::addToList($a_usr_id)) {
            return false;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $log->lso()->info('Raise new event: Modules/LearningSerquence addToList.');
        $app_event_handler->raise(
            "Modules/LearningSequence",
            'addToWaitingList',
            array(
                'obj_id' => $this->getObjId(),
                'usr_id' => $a_usr_id
            )
        );

        return true;
    }
}
