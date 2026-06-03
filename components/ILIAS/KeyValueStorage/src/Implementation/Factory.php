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

namespace ILIAS\KeyValueStorage\Implementation;

use ILIAS\KeyValueStorage\Exception\StorageNotAvailableException;
use ILIAS\KeyValueStorage\Factory as FactoryInterface;
use ILIAS\KeyValueStorage\Implementation\StorageBackend;
use ILIAS\KeyValueStorage\StorageProvider;
use ILIAS\KeyValueStorage\Storages;

/**
 * Resolves storage instances from contributed providers.
 */
final readonly class Factory implements FactoryInterface
{
    /** @var array<string, StorageProvider> */
    private array $providers_by_backend;

    /**
     * @param list<StorageProvider> $providers
     */
    public function __construct(array $providers)
    {
        $providers_by_backend = [];
        foreach ($providers as $provider) {
            $providers_by_backend[$provider->backend()->value] = $provider;
        }

        $this->providers_by_backend = $providers_by_backend;
    }

    public function session(): Storages
    {
        return $this->provider(StorageBackend::SESSION);
    }

    public function persistent(): Storages
    {
        return $this->provider(StorageBackend::PERSISTENT);
    }

    private function provider(StorageBackend $backend): StorageProvider
    {
        return $this->providers_by_backend[$backend->value]
            ?? throw new StorageNotAvailableException($backend);
    }
}
