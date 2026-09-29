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

use ILIAS\Authentication\KeyValueStorage\DicAuthenticatedSubjectPurge;
use ILIAS\Authentication\KeyValueStorage\UserDeletedSubjectPurgeHandler;
use ILIAS\KeyValueStorage\SubjectPurge;

/**
 * Legacy application event listener for the Authentication component.
 */
class ilAuthenticationAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        (new self())->purge($component, $event, $parameter);
    }

    private function purge(string $component, string $event, array $parameter): void
    {
        if (!$this->isUserDeleted($component, $event)) {
            return;
        }

        $user_id = (int) ($parameter['usr_id'] ?? 0);
        if ($user_id <= 0) {
            return;
        }

        $this->preparePurge()?->handle($user_id);
    }

    private function isUserDeleted(string $component, string $event): bool
    {
        if ($event !== 'deleteUser') {
            return false;
        }

        return $component === 'Services/User' || $component === 'components/ILIAS/User';
    }

    private function preparePurge(): ?UserDeletedSubjectPurgeHandler
    {
        global $DIC;

        if (!isset($DIC[SubjectPurge::class])) {
            return null;
        }

        return new UserDeletedSubjectPurgeHandler((new DicAuthenticatedSubjectPurge())->get());
    }
}
