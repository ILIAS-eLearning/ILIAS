<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Action that can be performed on a user
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserAction
{
    protected $text;
    protected $href;
    protected $data;
    protected $type;

    /**
     * Set text
     *
     * @param string $a_val text
     */
    public function setText($a_val)
    {
        $this->text = $a_val;
    }

    /**
     * Get text
     *
     * @return string text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set href
     *
     * @param string $a_val href
     */
    public function setHref($a_val)
    {
        $this->href = $a_val;
    }

    /**
     * Get href
     *
     * @return string href
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set type
     *
     * @param string $a_val type
     */
    public function setType($a_val)
    {
        $this->type = $a_val;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set data attributes
     *
     * @param array $a_val array of key => value pairs which will be transformed to data-<key>="value" attributes of link)
     */
    public function setData($a_val)
    {
        $this->data = $a_val;
    }

    /**
     * Get data attributes
     *
     * @return array array of key => value pairs which will be transformed to data-<key>="value" attributes of link
     */
    public function getData()
    {
        return $this->data;
    }
}
