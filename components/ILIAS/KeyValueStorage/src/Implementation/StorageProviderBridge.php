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

use ILIAS\KeyValueStorage\Storage;
use ILIAS\KeyValueStorage\Implementation\StorageBackend;
use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\KeyValueStorage\StoragePort;
use ILIAS\KeyValueStorage\StorageProvider;

/**
 * @internal Adapts a contributed storage port to the public storage provider contract.
 */
final readonly class StorageProviderBridge implements StorageProvider
{
    private string $scope_prefix;

    public function __construct(
        private StorageBackend $backend,
        private StoragePort $port,
        private NamespacedStorageFactory $storage_factory,
        private RequestScopeCache $request_scope_cache
    ) {
        $this->scope_prefix = $backend->value . ':';
    }

    public function backend(): StorageBackend
    {
        return $this->backend;
    }

    public function storage(StorageNamespace $namespace): Storage
    {
        return new RequestScopedStorage(
            $this->storage_factory->create($namespace, $this->port),
            $this->scope_prefix . $namespace->value(),
            $this->request_scope_cache
        );
    }
}
