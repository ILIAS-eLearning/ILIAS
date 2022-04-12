<?php namespace ILIAS\GlobalScreen\ScreenContext\AdditionalData;

use LogicException;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class Collection
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Collection
{
    private array $values = [];
    
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
    public function add(string $key, $value) : void
    {
        if ($this->exists($key)) {
            throw new LogicException("Key $key already exists.");
        }
        $this->values[$key] = $value;
    }
    
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->values[$key];
    }
    
    /**
     * @param string $key
     * @param        $expected_value
     * @return bool
     */
    public function is(string $key, $expected_value) : bool
    {
        return ($this->exists($key) && $this->get($key) === $expected_value);
    }
    
    /**
     * @param string $key
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
    public function replace(string $key, $value) : void
    {
        if (!$this->exists($key)) {
            throw new LogicException("Key $key does not exists.");
        }
        $this->values[$key] = $value;
    }
}
