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
 * A WOPI lock on a resource. The value is chosen by the WOPI client and is opaque to ILIAS.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final readonly class Lock
{
    public function __construct(
        public string $value,
        public int $expires,
        public int $user_id
    ) {
    }

    public function isExpired(int $now): bool
    {
        return $this->expires <= $now;
    }

    public function matches(string $value): bool
    {
        return hash_equals($this->value, $value);
    }
}
