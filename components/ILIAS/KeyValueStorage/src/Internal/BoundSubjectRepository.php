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
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\SubjectRepository;

/**
 * {@see Repository} fixed to one subject, so {@see NamespacedStore} can be reused.
 *
 * @internal
 */
final readonly class BoundSubjectRepository implements Repository
{
    public function __construct(
        private SubjectRepository $subjects,
        private SubjectId $subject
    ) {
    }

    public function has(StorageNamespace $namespace, string $key): bool
    {
        return $this->subjects->hasFor($this->subject, $namespace, $key);
    }

    public function read(StorageNamespace $namespace, string $key): ?string
    {
        return $this->subjects->readFor($this->subject, $namespace, $key);
    }

    public function readAll(StorageNamespace $namespace): array
    {
        return $this->subjects->readAllFor($this->subject, $namespace);
    }

    public function write(StorageNamespace $namespace, string $key, string $value): void
    {
        $this->subjects->writeFor($this->subject, $namespace, $key, $value);
    }

    public function remove(StorageNamespace $namespace, string $key): void
    {
        $this->subjects->removeFor($this->subject, $namespace, $key);
    }

    public function removeAll(StorageNamespace $namespace): void
    {
        $this->subjects->removeAllFor($this->subject, $namespace);
    }
}
