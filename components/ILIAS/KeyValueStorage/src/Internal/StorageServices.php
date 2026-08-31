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

namespace ILIAS\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Repository;
use ILIAS\KeyValueStorage\Services;
use ILIAS\KeyValueStorage\SessionRepository;
use ILIAS\KeyValueStorage\StorageNamespace;
use ILIAS\KeyValueStorage\Store;

/**
 * @internal
 */
final class StorageServices implements Services
{
    private readonly KeyRules $key_rules;

    private readonly Values $values;

    /** @var array<string, Store> */
    private array $stores = [];

    public function __construct(
        private readonly SessionRepository $session,
        private readonly Repository $persistent
    ) {
        $this->key_rules = new KeyRules();
        $this->values = new Values();
    }

    public function session(StorageNamespace $namespace): Store
    {
        return $this->store('session', $namespace, $this->session);
    }

    public function persistent(StorageNamespace $namespace): Store
    {
        return $this->store('persistent', $namespace, $this->persistent);
    }

    private function store(string $scope, StorageNamespace $namespace, Repository $repository): Store
    {
        return $this->stores[$scope . KeyRules::SEPARATOR . $namespace->value()] ??= new NamespacedStore(
            $namespace,
            $repository,
            $this->key_rules,
            $this->values
        );
    }
}
