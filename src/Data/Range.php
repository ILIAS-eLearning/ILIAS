<?php declare(strict_types=1);

namespace ILIAS\Data;

/**
 * A simple class to express a naive range of whole positive numbers.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Range
{
    /**
     * @var integer
     */
    protected $start;

    /**
     * @var integer
     */
    protected $length;


    public function __construct(int $start, int $length)
    {
        $this->checkStart($start);
        $this->checkLength($length);
        $this->start = $start;
        $this->length = $length;
    }

    protected function checkStart(int $start)
    {
        if ($start < 0) {
            throw new \InvalidArgumentException("Start must be a positive number (or 0)", 1);
        }
    }

    protected function checkLength(int $length)
    {
        if ($length < 1) {
            throw new \InvalidArgumentException("Length must be larger than 1", 1);
        }
    }

    public function unpack() : array
    {
        return [$this->start, $this->length];
    }

    public function getStart() : int
    {
        return $this->start;
    }

    public function getLength() : int
    {
        return $this->length;
    }

    public function getEnd() : int
    {
        return $this->start + $this->length;
    }

    public function withStart(int $start) : Range
    {
        $this->checkStart($start);
        $clone = clone $this;
        $clone->start = $start;
        return $clone;
    }

    public function withLength(int $length) : Range
    {
        $this->checkLength($length);
        $clone = clone $this;
        $clone->length = $length;
        return $clone;
    }
}
