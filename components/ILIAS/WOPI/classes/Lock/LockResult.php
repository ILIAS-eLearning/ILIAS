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

namespace ILIAS\WOPI\Lock;

/**
 * Outcome of a lock operation. On a conflict, $current_lock carries the lock the WOPI client
 * must be told about in the X-WOPI-Lock header - an empty string if the resource is unlocked.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final readonly class LockResult
{
    private function __construct(
        public bool $success,
        public string $current_lock,
        public ?string $reason
    ) {
    }

    public static function success(): self
    {
        return new self(true, '', null);
    }

    public static function conflict(string $current_lock, string $reason): self
    {
        return new self(false, $current_lock, $reason);
    }
}
