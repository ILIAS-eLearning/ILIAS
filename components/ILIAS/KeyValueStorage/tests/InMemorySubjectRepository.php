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

namespace ILIAS\Tests\KeyValueStorage;

use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\SubjectRepository;

class InMemorySubjectRepository implements SubjectRepository
{
    /** @var array<string, array<string, array<string, string>>> keyed by "provider:id" */
    public array $entries = [];

    public int $remove_subject_calls = 0;

    public int $remove_subjects_calls = 0;

    public function hasFor(SubjectId $subject, StorageNamespace $namespace, string $key): bool
    {
        return isset($this->entries[$this->keyOf($subject)][$namespace->value()][$key]);
    }

    public function readFor(SubjectId $subject, StorageNamespace $namespace, string $key): ?string
    {
        return $this->entries[$this->keyOf($subject)][$namespace->value()][$key] ?? null;
    }

    public function readAllFor(SubjectId $subject, StorageNamespace $namespace): array
    {
        return $this->entries[$this->keyOf($subject)][$namespace->value()] ?? [];
    }

    public function writeFor(SubjectId $subject, StorageNamespace $namespace, string $key, string $value): void
    {
        $this->entries[$this->keyOf($subject)][$namespace->value()][$key] = $value;
    }

    public function removeFor(SubjectId $subject, StorageNamespace $namespace, string $key): void
    {
        unset($this->entries[$this->keyOf($subject)][$namespace->value()][$key]);
    }

    public function removeAllFor(SubjectId $subject, StorageNamespace $namespace): void
    {
        unset($this->entries[$this->keyOf($subject)][$namespace->value()]);
    }

    public function removeSubject(SubjectId $subject): void
    {
        $this->remove_subject_calls++;
        unset($this->entries[$this->keyOf($subject)]);
    }

    public function removeSubjects(array $subjects): void
    {
        $this->remove_subjects_calls++;
        if ($subjects === []) {
            return;
        }

        foreach ($subjects as $subject) {
            if (!$subject instanceof SubjectId) {
                throw new \InvalidArgumentException('Expected a subject id.');
            }
            $this->removeSubject($subject);
        }
    }

    private function keyOf(SubjectId $subject): string
    {
        return $subject->provider() . ':' . $subject->id();
    }
}
