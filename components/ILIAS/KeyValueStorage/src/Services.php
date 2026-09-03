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

namespace ILIAS\KeyValueStorage;

/**
 * Entry point to the namespace-scoped key-value storages of ILIAS.
 *
 * The scope is chosen by the accessor: session storage is bound to the current
 * user session, persistent storage survives session boundaries until it is
 * changed or cleared.
 */
interface Services
{
    /**
     * Storage bound to the current user session.
     *
     * @param list<string> $namespace namespace segments; joined with "." internally
     */
    public function session(array $namespace): Store;

    /**
     * Storage that survives session boundaries until changed or cleared.
     *
     * This storage has no subject: it is shared by every user of the
     * installation. There is no per-user storage yet, and encoding a user id
     * into the namespace or the key is not a supported substitute - such rows
     * cannot be found or removed when the account is deleted.
     *
     * @param list<string> $namespace namespace segments; joined with "." internally
     */
    public function persistent(array $namespace): Store;
}
