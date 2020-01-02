<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/EventHandling/interfaces/interface.ilAppEventListener.php';

/**
 * Class ilContactAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilContactAppEventListener implements ilAppEventListener
{
    /**
     * {@inheritdoc}
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        global $DIC;

        if ('Services/User' == $a_component && 'deleteUser' == $a_event) {
            require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
            ilBuddyList::getInstanceByUserId($a_parameter['usr_id'])->destroy();

            require_once 'Services/Contact/classes/class.ilMailingList.php';
            ilMailingList::removeAssignmentsByUserId($a_parameter['usr_id']);
        }

        if ('Services/Contact' == $a_component && 'contactRequested' == $a_event) {
            $notification = new ilBuddySystemNotification($DIC->user(), $DIC->settings());
            $notification->setRecipientIds(array($a_parameter['usr_id']));
            $notification->send();
        }
    }
}
