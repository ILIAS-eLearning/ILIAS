<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup
 */
class ilCalFileZipJob implements \ILIAS\BackgroundTasks\Task\Job
{
    protected $file;

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return "mytype";
    }


    /**
     * Run
     *
     * @param
     */
    public function run(array $values, \ILIAS\BackgroundTasks\Observer $observer)
    {
        $this->file = current($values);
    }

    /**
     * Get input types
     *
     * @param
     * @return
     */
    public function getInputTypes()
    {
        return array("string");
    }

    /**
     * Set input		// what about run input?
     *
     * @param
     */
    public function setInput(array $values)
    {
        $this->file = current($values);
    }


    /**
     *
     *
     * @param
     * @return
     */
    public function getOutputType()
    {
        return "string";
    }

    /**
     * Get output
     *
     * @param
     * @return
     */
    public function getOutput()
    {
        return $this->file;
    }


    /**
     *
     *
     * @param
     * @return
     */
    public function isStateless()
    {
        return false;
    }
}
