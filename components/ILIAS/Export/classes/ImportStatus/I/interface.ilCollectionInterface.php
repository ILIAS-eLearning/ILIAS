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

namespace ImportStatus\I;

use Countable;
use ImportStatus\I\Content\ilHandlerInterface as ilImportStatusContentHandlerInterface;
use ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;
use ImportStatus\StatusType;
use Iterator;

interface ilCollectionInterface extends Iterator, Countable
{
    public function hasStatusType(StatusType $type): bool;

    public function withAddedStatus(ilImportStatusHandlerInterface $import_status): ilCollectionInterface;

    public function getCollectionOfAllByType(StatusType $type): ilCollectionInterface;

    public function getMergedCollectionWith(ilCollectionInterface $other): ilCollectionInterface;

    public function withNumberingEnabled(bool $enabled): ilCollectionInterface;

    public function toString(StatusType ...$types): string;

    public function mergeContentToElements(
        ilImportStatusContentHandlerInterface $content,
        bool $at_front = true
    ): ilCollectionInterface;

    /**
     * @return ilImportStatusHandlerInterface[]
     */
    public function toArray(): array;

    public function current(): ilImportStatusHandlerInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;

    public function count(): int;
}
