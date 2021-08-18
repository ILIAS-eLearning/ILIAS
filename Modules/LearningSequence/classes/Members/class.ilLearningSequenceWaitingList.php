<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilLearningSequenceWaitingList extends ilWaitingList
{
    public function addToList($usr_id)
    {
        global $DIC;

        $app_event_handler = $DIC->event();
        $log = $DIC->logger();
        
        if (!parent::addToList($usr_id)) {
            return false;
        }

        $log->lso()->info('Raise new event: Modules/LearningSerquence addToList.');
        $app_event_handler->raise(
            "Modules/LearningSequence",
            'addToWaitingList',
            array(
                'obj_id' => $this->getObjId(),
                'usr_id' => $usr_id
            )
        );

        return true;
    }
}
