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

use ILIAS\KeyValueStorage\Subject\SubjectResolver;

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
     * installation and only reads rows whose subject is empty. Per-subject
     * state belongs in {@see self::persistentFor()}. Encoding a subject into
     * the namespace or the key is not a supported substitute.
     *
     * @param list<string> $namespace namespace segments; joined with "." internally
     */
    public function persistent(array $namespace): Store;

    /**
     * Persistent storage for the subject the resolver names.
     *
     * Anonymous and any other non-named subject are rejected. The subject is a
     * parameter of the storage, never part of the namespace or the key.
     *
     * @param list<string> $namespace namespace segments; joined with "." internally
     * @throws \InvalidArgumentException if the resolver does not name a subject
     */
    public function persistentFor(SubjectResolver $subjects, array $namespace): Store;
}
