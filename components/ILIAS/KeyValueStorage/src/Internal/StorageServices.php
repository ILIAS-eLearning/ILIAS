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
use ILIAS\KeyValueStorage\Store;
use ILIAS\KeyValueStorage\Subject\SubjectResolver;
use ILIAS\KeyValueStorage\SubjectRepository;
use ILIAS\Refinery\Factory as Refinery;

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
        private readonly Repository $persistent,
        private readonly SubjectRepository $subjects,
        Refinery $refinery
    ) {
        $this->key_rules = new KeyRules();
        $this->values = new Values($refinery);
    }

    public function session(array $namespace): Store
    {
        return $this->store('session', new StorageNamespace($namespace), $this->session);
    }

    public function persistent(array $namespace): Store
    {
        return $this->store('persistent', new StorageNamespace($namespace), $this->persistent);
    }

    public function persistentFor(SubjectResolver $subjects, array $namespace): Store
    {
        $subject = $subjects->subject();
        if (!$subject->isNamed()) {
            throw new \InvalidArgumentException('Persistent subject storage requires a named subject.');
        }

        return $this->store(
            'subject' . KeyRules::SEPARATOR . $subject->id()->storageSegment(),
            new StorageNamespace($namespace),
            new BoundSubjectRepository($this->subjects, $subject->id())
        );
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
