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

namespace ILIAS\WOPI\Discovery;

/**
 * Fetches the proof keys from the discovery again when a request could not be verified with
 * the stored ones - the WOPI client may have rotated its keys since the last crawl, see
 * https://learn.microsoft.com/en-us/microsoft-365/cloud-storage-partner-program/online/scenarios/proofkeys
 *
 * Every request failing the verification would trigger a fetch, so it happens at most once
 * within MIN_INTERVAL. A failed fetch keeps the stored keys.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ProofKeyRefresh
{
    public const MIN_INTERVAL = 5 * 60;
    private const LAST_REFRESH = 'wopi_proof_refreshed_at';

    /**
     * @var \Closure(): int
     */
    private \Closure $clock;

    /**
     * @param \Closure(): ?ProofKeys $fetch reads the proof keys from the discovery
     * @param \Closure(): int|null  $clock returns the current unix timestamp, for testing
     */
    public function __construct(
        private ProofKeyRepository $repository,
        private \ilSetting $settings,
        private \Closure $fetch,
        ?\Closure $clock = null
    ) {
        $this->clock = $clock ?? static fn(): int => time();
    }

    /**
     * @return ProofKeys|null the keys just fetched, null if nothing was fetched
     */
    public function __invoke(): ?ProofKeys
    {
        $now = ($this->clock)();
        if ($now - (int) $this->settings->get(self::LAST_REFRESH, '0') < self::MIN_INTERVAL) {
            return null;
        }
        $this->settings->set(self::LAST_REFRESH, (string) $now);

        try {
            $keys = ($this->fetch)();
        } catch (\Throwable) {
            return null;
        }
        if ($keys === null) {
            return null;
        }
        $this->repository->store($keys);

        return $keys;
    }
}
