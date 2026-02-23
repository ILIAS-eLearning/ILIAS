<?php

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

declare(strict_types=1);

use ILIAS\Notifications\Repository\PushRepository;

class ilNotificationsAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        if ($event === 'deleteUser') {
            global $DIC;
            $repo = new PushRepository($DIC->database(), new ilObjUser($parameter['usr_id']));
            foreach ($repo->getUserSubscriptions() as $subscription) {
                $repo->deleteSubscription($subscription->getAuth());
            }
        }
    }
}
