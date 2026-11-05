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
 * What a session of ILIAS started with the other side of LTI Advantage and waits to be answered: a login
 * waiting for its authentication request, a Deep Linking request waiting for its response, a Dynamic
 * Registration waiting for the tool. Each one is kept by its key for a while, and an expired one is gone.
 *
 * The requests belong to the session, but are not kept in it: ILIAS writes the whole session back when a
 * request ends, so a request running in parallel in the same session would remove them again.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final readonly class ilLTIAdvantagePendingRequests
{
    private const string CREATED = 'created';

    private ilLTIAdvantagePendingRequestStorage $storage;
    private string $session;

    /**
     * @param string $kind     what the requests are, which keeps them apart from the other ones of the session
     * @param int    $lifetime seconds a request may wait
     * @param string|null $session_id the session the requests belong to, the current one by default
     */
    public function __construct(
        private string $kind,
        private int $lifetime,
        ?ilLTIAdvantagePendingRequestStorage $storage = null,
        ?string $session_id = null
    ) {
        global $DIC;

        $this->storage = $storage ?? new ilLTIAdvantagePendingRequestDBStorage($DIC->database());
        $session_id ??= session_id();
        $this->session = $session_id !== false && $session_id !== '' ? hash('sha256', $session_id) : '';
    }

    /**
     * @param array $request
     */
    public function add(string $key, array $request): void
    {
        if ($this->session === '') {
            return;
        }
        $now = time();
        $this->storage->deleteOlderThan($this->kind, $now - $this->lifetime);
        $this->storage->write($this->session, $this->kind, $this->hashKey($key), $request, $now);
    }

    /**
     * @return array|null the request, null when it is not waiting
     */
    public function get(string $key): ?array
    {
        if ($this->session === '') {
            return null;
        }
        $kept = $this->storage->read($this->session, $this->kind, $this->hashKey($key));

        return $this->isWaiting($kept) ? [self::CREATED => $kept[1]] + $kept[0] : null;
    }

    /**
     * Ends the request, answered or not. Only one of several requests taking it at the same time gets it.
     *
     * @return array|null the request, null when it was not waiting
     */
    public function take(string $key): ?array
    {
        if ($this->session === '') {
            return null;
        }
        $kept = $this->storage->read($this->session, $this->kind, $this->hashKey($key));
        if ($kept === null || !$this->storage->delete($this->session, $this->kind, $this->hashKey($key))) {
            return null;
        }

        return $this->isWaiting($kept) ? [self::CREATED => $kept[1]] + $kept[0] : null;
    }

    private function hashKey(string $key): string
    {
        return hash('sha256', $key);
    }

    /**
     * @param array{0: array, 1: int}|null $kept
     */
    private function isWaiting(?array $kept): bool
    {
        return $kept !== null && $kept[1] >= time() - $this->lifetime;
    }
}
