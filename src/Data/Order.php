<?php

declare(strict_types=1);

namespace ILIAS\Data;

/**
 * Both the subject and the direction need to be specified when expressing an order.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Order
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    /**
     * @var array<string, string>
     */
    protected array $order = [];

    public function __construct(string $subject, string $direction)
    {
        $this->checkDirection($direction);
        $this->order[$subject] = $direction;
    }

    protected function checkSubject(string $subject): void
    {
        if (array_key_exists($subject, $this->order)) {
            throw new \InvalidArgumentException("already sorted by subject '$subject'", 1);
        }
    }

    protected function checkDirection(string $direction): void
    {
        if ($direction !== self::ASC && $direction !== self::DESC) {
            throw new \InvalidArgumentException("Direction bust be Order::ASC or Order::DESC.", 1);
        }
    }

    public function append(string $subject, string $direction): Order
    {
        $this->checkSubject($subject);
        $this->checkDirection($direction);
        $clone = clone $this;
        $clone->order[$subject] = $direction;
        return $clone;
    }

    /**
     * @return array<string, string>
     */
    public function get(): array
    {
        return $this->order;
    }

    public function join($init, callable $fn)
    {
        $ret = $init;
        foreach ($this->order as $key => $value) {
            $ret = $fn($ret, $key, $value);
        }
        return $ret;
    }
}
