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
     * @var array
     */
    private array $data;

    /**
     * ilCtrlArrayIterator Constructor
     *
     * @param string[]
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function current(): ?string
    {
        if ($this->valid()) {
            return current($this->data);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @inheritDoc
     */
    public function key(): ?string
    {
        if ($this->valid()) {
            return key($this->data);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        $value = current($this->data);
        $key = key($this->data);

        if (null === $key) {
            return false;
        }

        if (!is_string($value) || !is_string($key)) {
            $this->next();
            return $this->valid();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->data);
    }
}
