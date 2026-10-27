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

namespace ILIAS\Language\Tests;

use ILIAS\KeyValueStorage\Services;
use ILIAS\KeyValueStorage\Store;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\KeyValueStorage\Subject\SubjectResolver;

/** Session scope only, values are kept as given (no JSON round trip). */
final class InMemoryKeyValueStorage implements Services
{
    /** @var array<string, InMemoryKeyValueStore> */
    private array $sessions = [];

    public function session(array $namespace): InMemoryKeyValueStore
    {
        return $this->sessions[implode('.', $namespace)] ??= new InMemoryKeyValueStore();
    }

    public function persistent(array $namespace): Store
    {
        throw new \LogicException('not supported');
    }

    public function persistentFor(SubjectResolver $subjects, array $namespace): Store
    {
        throw new \LogicException('not supported');
    }

    public function purgeSubject(SubjectId $subject): void
    {
        throw new \LogicException('not supported');
    }
}
