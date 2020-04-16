<?php declare(strict_types=1);

namespace ILIAS\Data;

/**
 * Both the subject and the direction need to be specified when expressing an order.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Order
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var mixed
     */
    protected $direction;

    public function __construct(string $subject, $direction)
    {
        $this->checkDirection($direction);
        $this->order[] = [$subject, $direction];
    }

    protected function checkDirection($direction)
    {
        if ($direction !== self::ASC && $direction !== self::DESC) {
            throw new \InvalidArgumentException("Direction bust be Order::ASC or Order::DESC.", 1);
        }
    }

    public function append(string $subject, $direction) : Order
    {
        $this->checkDirection($direction);
        $clone = clone $this;
        $clone->order[] = [$subject, $direction];
        return $clone;
    }

    public function get() : array
    {
        return $this->order;
    }

    public function join() : string
    {
        return implode(
            ', ',
            array_map(
                function ($entry) {
                    return implode($entry, ' ');
                },
                $this->order
            )
        );
    }
}
