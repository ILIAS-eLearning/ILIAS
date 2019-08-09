<?php

namespace ILIAS\UI\Implementation\Component;

/**
 * Class Signal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class Signal implements \ILIAS\UI\Component\Signal
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function addOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    protected function getOption($key)
    {
        return (isset($this->options[$key])) ? $this->options[$key] : null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}
