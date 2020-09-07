<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/ActiveRecord/class.ActiveRecord.php");

/**
 * Item group active record class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilItemGroupAR extends ActiveRecord
{
    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'itgr_data';
    }

    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     * @con_length     4
     * @con_sequence   false
     */
    protected $id;

    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     * @con_is_notnull false
     */
    protected $hide_title = '';

    /**
     * @var int
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     * @con_is_notnull false
     */
    protected $behaviour = 0;

    /**
     * Get ID
     *
     * @return int ID
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set ID
     *
     * @param int $id ID
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set hide title
     *
     * @param bool $a_hide_title hide title
     */
    public function setHideTitle($a_hide_title)
    {
        $this->hide_title = $a_hide_title;
    }


    /**
     * Get hide title
     *
     * @return bool hide title
     */
    public function getHideTitle()
    {
        return $this->hide_title;
    }

    /**
     * Set behaviour
     *
     * @param int $a_val behaviour
     */
    public function setBehaviour($a_val)
    {
        $this->behaviour = $a_val;
    }


    /**
     * Get behaviour
     *
     * @return int behaviour
     */
    public function getBehaviour()
    {
        return $this->behaviour;
    }
}
