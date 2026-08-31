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

namespace ILIAS;

use ILIAS\Component\Component;
use ILIAS\Database\Connection;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\Agent;

class KeyValueStorage implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = KeyValueStorage\Services::class;

        // the session belongs to Authentication, so the session scope is the
        // only one this component cannot store by itself.
        $define[] = KeyValueStorage\SessionRepository::class;

        $implement[KeyValueStorage\Services::class] = static fn() =>
            new KeyValueStorage\Internal\StorageServices(
                $use[KeyValueStorage\SessionRepository::class],
                $internal[KeyValueStorage\Internal\DatabaseRepository::class]
            );

        $contribute[Agent::class] = static fn(): Agent =>
            new KeyValueStorage\Setup\Agent(
                $pull[Refinery::class]
            );

        $internal[KeyValueStorage\Internal\DatabaseRepository::class] = static fn() =>
            new KeyValueStorage\Internal\DatabaseRepository(
                $pull[Connection::class]
            );
    }
}
