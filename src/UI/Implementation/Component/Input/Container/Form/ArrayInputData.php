<?php

/* Copyright (c) 2020 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Implementation\Component\Input\InputData;

/**
 * Class ArrayInputData
 *
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Input\Container\Form
 */
class ArrayInputData implements InputData
{
    /**
     * @var    array
     */
    protected array $data;

    /**
     * ArrayInputData Constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }


    /**
     * @inheritdocs
     */
    public function get($name)
    {
        if (!isset($this->data[$name])) {
            throw new \LogicException("'$name' is not contained in provided data.");
        }

        return $this->data[$name];
    }


    /**
     * @inheritdocs
     */
    public function getOr($name, $default)
    {
        return $this->data[$name] ?? $default;
    }
}
