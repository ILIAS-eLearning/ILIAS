<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents a set of collected user actions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionCollection
{
    /**
     * @var array
     */
    protected $actions = array();

    /**
     * Get instance
     *
     * @return ilUserActionCollection user collection
     */
    public static function getInstance()
    {
        return new ilUserActionCollection();
    }

    /**
     * Add action
     *
     * @param ilUserAction $a_action action object
     */
    public function addAction(ilUserAction $a_action)
    {
        $this->actions[] = $a_action;
    }

    /**
     * Get users
     *
     * @return array array of user ids (integer)
     */
    public function getActions()
    {
        return $this->actions;
    }
}
