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

namespace ImportHandler\I\File\Validation\Set;

use Iterator;
use Countable;
use ImportHandler\I\File\Validation\Set\ilHandlerInterface as ilFileValidationPairHandlerInterface;

interface ilCollectionInterface extends Iterator, Countable
{
    public function withElement(ilFileValidationPairHandlerInterface $element): ilCollectionInterface;

    public function withMerged(ilCollectionInterface $other): ilCollectionInterface;

    /**
     * @return ilFileValidationPairHandlerInterface[]
     */
    public function toArray(): array;

    public function current(): ilFileValidationPairHandlerInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;
}
