<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContactAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilContactAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        global $DIC;

        if ('Services/User' == $a_component && 'deleteUser' == $a_event) {
            ilBuddyList::getInstanceByUserId((int) $a_parameter['usr_id'])->destroy();
            ilMailingList::removeAssignmentsByUserId((int) $a_parameter['usr_id']);
        }

        if ('Services/Contact' == $a_component && 'contactRequested' == $a_event) {
            $notification = new ilBuddySystemNotification($DIC->user(), $DIC->settings());
            $notification->setRecipientIds([(int) $a_parameter['usr_id']]);
            $notification->send();
        }
    }
}