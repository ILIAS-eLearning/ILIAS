<?php namespace ILIAS\NavigationContext\AdditionalData;

use LogicException;

/**
 * Class Collection
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Collection
{

    /**
     * @var array
     */
    private $values = [];


    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->values;
    }


    /**
     * @param string $key
     * @param        $value
     */
    public function add(string $key, $value)
    {
        if ($this->exists($key)) {
            throw new LogicException("Key {$key} already exists.");
        }
        $this->values[$key] = $value;
    }


    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->values[$key];
    }


    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key) : bool
    {
        return isset($this->values[$key]);
    }


    /**
     * @param string $key
     * @param        $value
     */
    public function replace(string $key, $value)
    {
        if (!$this->exists($key)) {
            throw new LogicException("Key {$key} does not exists.");
        }
        $this->values[$key] = $value;
    }
}
