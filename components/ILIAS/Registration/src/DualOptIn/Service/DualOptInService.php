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

namespace ILIAS\Registration\DualOptIn\Service;

use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationExpiredException;
use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationNotFoundException;
use ILIAS\Registration\DualOptIn\Exception\PendingRegistrationAlreadyConfirmedException;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationHash;

interface DualOptInService
{
    /**
     * @throws PendingRegistrationNotFoundException
     * @throws PendingRegistrationExpiredException
     * @throws PendingRegistrationAlreadyConfirmedException
     * @throws \ilObjectNotFoundException
     */
    public function verifyHashAndActivateUser(PendingRegistrationHash $hash): \ilObjUser;

    public function distributeMailsOnRegistration(\ilObjUser $user): void;

    public function deleteExpiredUserObjects(?int $usr_id_to_prioritize = null): int;
}
