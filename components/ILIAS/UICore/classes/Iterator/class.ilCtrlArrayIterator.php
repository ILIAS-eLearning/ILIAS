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

/**
 * Class ilCtrlArrayIterator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayIterator implements ilCtrlIteratorInterface
{
    /**
     * @param array<string, string> $class_map
     */
    public function __construct(
        protected Iterator $command_class_iterator,
        protected array $class_map,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function current(): ?string
    {
        if ($this->valid()) {
            return $this->class_map[$this->key()] ?? null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->command_class_iterator->next();
    }

    /**
     * @inheritDoc
     */
    public function key(): ?string
    {
        if ($this->valid()) {
            return $this->command_class_iterator->current();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->command_class_iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->command_class_iterator->rewind();
    }
}
