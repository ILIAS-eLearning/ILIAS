<?php declare(strict_types=1);

namespace ILIAS\HTTP\Wrapper;

use ILIAS\Refinery\Factory;

/**
 * Class DirectArrayAccessDropInReplacement
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class DirectArrayAccessDropInReplacement implements \ArrayAccess
{

    /**
     * @var Factory
     */
    private $factory;
    /**
     * @var array
     */
    private $raw_values;


    /**
     * DirectValueAccessDropInReplacement constructor.
     *
     * @param Factory $factory
     * @param array   $raw_values
     */
    public function __construct(Factory $factory, array $raw_values)
    {
        $this->factory = $factory;
        $this->raw_values = $raw_values;
    }


    /**
     * @inheritDoc
     */
    public function offsetExists($offset) : bool
    {
        return is_array($this->raw_values) && isset($this->raw_values[$offset]);
    }


    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        if (is_array($this->raw_values[$offset])) {
            $this->factory->to()->dictOf($this->factory->to()->string())->transform((array) $this->raw_values[$offset]);
        }

        return $this->factory->to()->string()->transform((string) $this->raw_values[$offset]);
    }


    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value) : void
    {
        // throw new \LogicException("Modifying global Request-Array such as \$_GET is not allowed!");
        $this->raw_values[$offset] = $value;
    }


    /**
     * @inheritDoc
     */
    public function offsetUnset($offset) : void
    {
        throw new \LogicException("Modifying global Request-Array such as \$_GET is not allowed!");
    }
}
