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

/**
 * Where the requests of LTI Advantage a session waits to be answered are kept, by session, kind and key. Each
 * request is a row of its own, so requests running in parallel in the same session never overwrite each other.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
interface ilLTIAdvantagePendingRequestStorage
{
    /**
     * @return array{0: array, 1: int}|null the request and when it was kept, null when there is none
     */
    public function read(string $session, string $kind, string $key): ?array;

    /**
     * Keeps the request, replacing one with the same session, kind and key.
     *
     * @param array $request
     */
    public function write(string $session, string $kind, string $key, array $request, int $created): void;

    /**
     * @return bool whether there was a request to remove
     */
    public function delete(string $session, string $kind, string $key): bool;

    /**
     * Removes the requests of the kind kept before the given time, of all sessions.
     */
    public function deleteOlderThan(string $kind, int $created): void;
}
