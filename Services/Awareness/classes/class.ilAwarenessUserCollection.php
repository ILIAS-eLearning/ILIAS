<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Represents a set of collected users
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserCollection implements \Countable
{
    protected $users = array();

    /**
     * Get instance
     *
     * @return ilAwarenessUserCollection user collection
     */
    public static function getInstance()
    {
        return new ilAwarenessUserCollection();
    }

    /**
     * Add user
     *
     * @param integer $a_id user id
     */
    public function addUser($a_id)
    {
        $this->users[$a_id] = $a_id;
    }

    /**
     * Remove user
     *
     * @param integer $a_id user id
     */
    public function removeUser($a_id)
    {
        if (isset($this->users[$a_id])) {
            unset($this->users[$a_id]);
        }
    }

    /**
     * Get users
     *
     * @return array array of user ids (integer)
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->users);
    }
}
