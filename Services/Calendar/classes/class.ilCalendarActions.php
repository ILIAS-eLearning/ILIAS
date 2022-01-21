<?php declare(strict_types=1);

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Checks if certain actions can be performed
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup
 */
class ilCalendarActions
{
    protected static ?ilCalendarActions $instance = null;

    protected ilCalendarCategories $cats;
    private int $user_id;

    /**
     * Constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->user_id = $DIC->user()->getId();
        $this->cats = ilCalendarCategories::_getInstance($this->user_id);
        if ($this->cats->getMode() == ilCalendarCategories::MODE_UNDEFINED) {
            throw new ilCalCategoriesNotInitializedException(
                "ilCalendarActions needs ilCalendarCategories to be initialized for user " . $this->user_id
            );
        }
    }

    /**
     * Get instance
     *
     * @return ilCalendarActions
     */
    public static function getInstance() : ilCalendarActions
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check calendar editing
     */
    public function checkSettingsCal(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        return $info['accepted'];
    }

    /**
     * Check sharing (own) calendar
     */
    public function checkShareCal(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        return
            $info['type'] == ilCalendarCategory::TYPE_USR &&
            $info['obj_id'] == $this->user_id;
    }

    /**
     * Check un-sharing (other users) calendar
     *
     */
    public function checkUnshareCal(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if ($info['accepted']) {
            return true;
        }
        return false;
    }

    /**
     * Check synchronize remote calendar
     */
    public function checkSynchronizeCal(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if ($info['remote']) {
            return true;
        }
        return false;
    }

    /**
     * Check if adding an event is possible
     */
    public function checkAddEvent(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        return $info['editable'];
    }

    /**
     * Check if adding an event is possible
     */
    public function checkDeleteCal(int $a_cat_id) : bool
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
