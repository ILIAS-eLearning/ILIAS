<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Checks if certain actions can be performed
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup
 */
class ilCalendarActions
{
    /**
     * @var ilCalendarActions|null
     */
    protected static $instance = null;

    /**
     * @var ilCalendarCategories|null
     */
    protected $cats = null;

    /**
     * @var int user id
     */
    protected $user_id;

    /**
     * Constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->user_id = $DIC->user()->getId();

        include_once("./Services/Calendar/classes/class.ilCalendarCategories.php");
        $this->cats = ilCalendarCategories::_getInstance($this->user_id);
        if ($this->cats->getMode() == 0) {
            include_once("./Services/Calendar/exceptions/class.ilCalCategoriesNotInitializedException.php");
            throw new ilCalCategoriesNotInitializedException("ilCalendarActions needs ilCalendarCategories to be initialized for user " . $this->user_id . ".");
        }
    }

    /**
     * Get instance
     *
     * @return ilCalendarActions
     */
    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new ilCalendarActions();
        }
        return self::$instance;
    }

    /**
     * Check calendar editing
     *
     * @param int $a_cat_id calendar category id
     * @return bool
     */
    public function checkSettingsCal($a_cat_id)
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        return $info['settings'];
    }

    /**
     * Check sharing (own) calendar
     *
     * @param int $a_cat_id calendar category id
     * @return bool
     */
    public function checkShareCal($a_cat_id)
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if ($info['type'] == ilCalendarCategory::TYPE_USR && $info['obj_id'] == $this->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check un-sharing (other users) calendar
     *
     * @param int $a_cat_id calendar category id
     * @return bool
     */
    public function checkUnshareCal($a_cat_id)
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if ($info['accepted']) {
            return true;
        }

        return false;
    }

    /**
     * Check synchronize remote calendar
     *
     * @param int $a_cat_id calendar category id
     * @return bool
     */
    public function checkSynchronizeCal($a_cat_id)
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if ($info['remote']) {
            return true;
        }

        return false;
    }

    /**
     * Check if adding an event is possible
     *
     * @param int $a_cat_id calendar category id
     * @return bool
     */
    public function checkAddEvent($a_cat_id)
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        return $info['editable'];
    }

    /**
     * Check if adding an event is possible
     *
     * @param int $a_cat_id calendar category id
     * @return bool
     */
    public function checkDeleteCal($a_cat_id)
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if ($info['type'] == ilCalendarCategory::TYPE_USR && $info['obj_id'] == $this->user_id) {
            return true;
        }
        if ($info['type'] == ilCalendarCategory::TYPE_GLOBAL && $info['settings']) {
            return true;
        }

        return false;
    }
}
