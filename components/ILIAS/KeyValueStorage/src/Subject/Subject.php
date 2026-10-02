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

namespace ILIAS\KeyValueStorage\Subject;

/**
 * Who a persistent value belongs to.
 *
 * Global installation state is not a subject: it is {@see \ILIAS\KeyValueStorage\Services::persistent()}.
 * Construct a subject only through the named factories.
 */
final readonly class Subject
{
    private function __construct(private ?SubjectId $id)
    {
    }

    public static function anonymous(): self
    {
        return new self(null);
    }

    public static function named(SubjectId $id): self
    {
        return new self($id);
    }

    public function isAnonymous(): bool
    {
        return $this->id === null;
    }

    public function isNamed(): bool
    {
        return $this->id !== null;
    }

    public function id(): SubjectId
    {
        if ($this->id === null) {
            throw new \LogicException('Subject has no id.');
        }

        return $this->id;
    }
}
