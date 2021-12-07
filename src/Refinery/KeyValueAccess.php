<?php declare(strict_types=1);

namespace ILIAS\Refinery;

/**
 * Class KeyValueAccess
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class KeyValueAccess implements \ArrayAccess, \Countable
{

    /**
     * @var array
     */
    private $raw_values;
    /**
     * @var Transformation
     */
    protected $trafo;

    /**
     * KeyValueAccess constructor.
     * @param array          $raw_values
     * @param Transformation $trafo
     */
    public function __construct(array $raw_values, Transformation $trafo)
    {
        $this->trafo = $trafo;
        $this->raw_values = $raw_values;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->raw_values[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        return is_array($this->raw_values[$offset])
            ? array_map($this->getApplicator(), $this->raw_values[$offset])
            : $this->getApplicator()($this->raw_values[$offset]);
    }

    private function getApplicator() : \Closure
    {
        return function ($value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $value[$k] = $this->getApplicator()($v);
                }
                return $value;
            }
            return $this->trafo->transform($value);
        };
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value) : void
    {
        $this->raw_values[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset) : void
    {
        if ($this->offsetExists($offset)) {
            unset($this->raw_values[$offset]);
        }
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->raw_values);
    }
}
