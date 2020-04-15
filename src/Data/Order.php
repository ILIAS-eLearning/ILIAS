<?php declare(strict_types=1);

namespace ILIAS\Data;

/**
 * Both the subject and the direction need to be specified when expressing an order.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Order
{
    const ASC = 1;
    const DESC = -1;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var mixed
     */
    protected $direction;

    public function __construct(string $subject, $direction)
    {
        $this->checkDirection($direction);
        $this->subject = $subject;
        $this->direction = $direction;
    }

    protected function checkDirection($direction)
    {
        if ($direction !== self::ASC && $direction !== self::DESC) {
            throw new \InvalidArgumentException("Direction bust be Order::ASC or Order::DESC.", 1);
        }
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function withSubject(string $subject) : Order
    {
        $clone = clone $this;
        $clone->subject = $subject;
        return $clone;
    }

    public function withDirection($direction) : Order
    {
        $this->checkDirection($direction);
        $clone = clone $this;
        $clone->direction = $direction;
        return $clone;
    }
}
