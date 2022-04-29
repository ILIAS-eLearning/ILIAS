<?php declare(strict_types=1);

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
