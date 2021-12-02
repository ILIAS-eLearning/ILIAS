<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContactAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilContactAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        global $DIC;

        if ('Services/User' === $a_component && 'deleteUser' === $a_event) {
            ilBuddyList::getInstanceByUserId((int) $a_parameter['usr_id'])->destroy();
            $user = new ilObjUser();
            $user->setId((int) $a_parameter['usr_id']);

            $mailingLists = new ilMailingLists($user);
            $mailingLists->deleteAssignments();
        }

        if ('Services/Contact' === $a_component && 'contactRequested' === $a_event) {
            $notification = new ilBuddySystemNotification($DIC->user(), $DIC->settings());
            $notification->setRecipientIds([(int) $a_parameter['usr_id']]);
            $notification->send();
        }
    }
}
