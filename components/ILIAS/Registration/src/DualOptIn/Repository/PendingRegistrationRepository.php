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

namespace ILIAS\Registration\DualOptIn\Repository;

use ILIAS\Registration\DualOptIn\Entity\PendingRegistration;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationHash;
use ILIAS\Registration\DualOptIn\ValueObjects\PendingRegistrationId;

interface PendingRegistrationRepository
{
    public function nextIdentity(): PendingRegistrationId;

    public function findNewHash(): PendingRegistrationHash;

    public function store(PendingRegistration $reg): void;

    public function findByHashValue(string $hash_value): ?PendingRegistration;

    public function delete(PendingRegistration ...$pending_registrations): void;

    public function deleteByUserId(int $usr_id): void;

    /** @return list<PendingRegistration> */
    public function findExpired(int $cutoff_ts, ?int $usr_id_to_prioritize = null): array;
}
