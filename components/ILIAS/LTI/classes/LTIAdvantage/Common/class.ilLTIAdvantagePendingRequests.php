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
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final readonly class ilLTIAdvantagePendingRequests
{
    private const string CREATED = 'created';

    /**
     * @param int $lifetime seconds a request may wait
     */
    public function __construct(private string $session_key, private int $lifetime)
    {
    }

    /**
     * @param array $request
     */
    public function add(string $key, array $request): void
    {
        $requests = $this->getAll();
        $requests[$key] = [self::CREATED => time()] + $request;
        ilSession::set($this->session_key, $requests);
    }

    /**
     * @return array|null the request, null when it is not waiting
     */
    public function get(string $key): ?array
    {
        return $this->getAll()[$key] ?? null;
    }

    /**
     * Ends the request, answered or not.
     *
     * @return array|null the request, null when it was not waiting
     */
    public function take(string $key): ?array
    {
        $requests = $this->getAll();
        $request = $requests[$key] ?? null;
        unset($requests[$key]);
        ilSession::set($this->session_key, $requests);

        return $request;
    }

    /**
     * @return array the requests still waiting, by key
     */
    private function getAll(): array
    {
        $requests = ilSession::get($this->session_key);

        return array_filter(
            is_array($requests) ? $requests : [],
            fn($request): bool => is_array($request) && ($request[self::CREATED] ?? 0) >= time() - $this->lifetime
        );
    }
}
