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

namespace ILIAS\KeyValueStorage;

use ILIAS\KeyValueStorage\Internal\StorageNamespace;
use ILIAS\KeyValueStorage\Subject\SubjectId;

/**
 * Subject-scoped operations on the same persistent table as {@see Repository}.
 *
 * Values are passed through as opaque strings. This is not a consumer type:
 * consumers use {@see Services::persistentFor()} and {@see Services::purgeSubject()}.
 */
interface SubjectRepository
{
    public function hasFor(SubjectId $subject, StorageNamespace $namespace, string $key): bool;

    /**
     * @return string|null null if the key is not present
     */
    public function readFor(SubjectId $subject, StorageNamespace $namespace, string $key): ?string;

    /**
     * Every present entry of one subject in one namespace.
     *
     * @return array<string, string> key => stored string; empty if that scope holds nothing
     */
    public function readAllFor(SubjectId $subject, StorageNamespace $namespace): array;

    public function writeFor(SubjectId $subject, StorageNamespace $namespace, string $key, string $value): void;

    public function removeFor(SubjectId $subject, StorageNamespace $namespace, string $key): void;

    public function removeAllFor(SubjectId $subject, StorageNamespace $namespace): void;

    /**
     * Removes every entry of one subject, in every namespace.
     */
    public function removeSubject(SubjectId $subject): void;

    /**
     * @param list<SubjectId> $subjects
     */
    public function removeSubjects(array $subjects): void;
}
