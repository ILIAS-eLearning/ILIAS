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

namespace ILIAS\Data;

/**
 * A simple class to express a range of whole positive numbers.
 * Range is a half-open interval (right-open): [a,b) = {x ∈ ℕ: a ≤ x < b}.
 * Since the endpoint is excluded, a Range of 0,3 means: records 0,1,2, but not 3.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Range
{
    protected int $start;
    protected int $length;

    public function __construct(int $start, int $length)
    {
        $this->checkStart($start);
        $this->checkLength($length);
        $this->start = $start;
        $this->length = $length;
    }

    protected function checkStart(int $start): void
    {
        if ($start < 0) {
            throw new \InvalidArgumentException("Start must be a positive number (or 0)", 1);
        }
    }

    protected function checkLength(int $length): void
    {
        if ($length < 0) {
            throw new \InvalidArgumentException("Length must be larger or equal then 0", 1);
        }
    }

    public function unpack(): array
    {
        return [$this->start, $this->length];
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * getEnd will return the (excluded) endpoint.
     * For a Range of (4,2), e.g.: getEnd is 4 + 2 = 6, so valid records are 4 and 5, but not 6.
     */
    public function getEnd(): int
    {
        if ($this->length === PHP_INT_MAX) {
            return PHP_INT_MAX;
        }

        return $this->start + $this->length;
    }

    public function withStart(int $start): Range
    {
        $this->checkStart($start);
        $clone = clone $this;
        $clone->start = $start;
        return $clone;
    }

    public function withLength(int $length): Range
    {
        $this->checkLength($length);
        $clone = clone $this;
        $clone->length = $length;
        return $clone;
    }

    /**
     * This will create a range that is guaranteed to not exceed $max.
     */
    public function croppedTo(int $max): Range
    {
        if ($max > $this->getEnd()) {
            return $this;
        }

        if ($this->getStart() > $max) {
            return new self($max, 0);
        }

        return $this->withLength($max - $this->getStart());
    }
}
