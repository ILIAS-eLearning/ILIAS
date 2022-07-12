<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Checks if certain actions can be performed
 * @author Alex Killing <alex.killing@gmx.de>
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
        return (bool) ($info['accepted'] ?? false);
    }

    /**
     * Check sharing (own) calendar
     */
    public function checkShareCal(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        return
            ($info['type'] ?? 0) == ilCalendarCategory::TYPE_USR &&
            ($info['obj_id'] ?? '') == $this->user_id;
    }

    /**
     * Check un-sharing (other users) calendar
     */
    public function checkUnshareCal(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if ($info['accepted'] ?? false) {
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
        if ($info['remote'] ?? false) {
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
        return $info['editable'] ?? false;
    }

    /**
     * Check if adding an event is possible
     */
    public function checkDeleteCal(int $a_cat_id) : bool
    {
        $info = $this->cats->getCategoryInfo($a_cat_id);
        if (($info['type'] ?? 0) == ilCalendarCategory::TYPE_USR && ($info['obj_id'] ?? 0) == $this->user_id) {
            return true;
        }
        if (($info['type'] ?? 0) == ilCalendarCategory::TYPE_GLOBAL && ($info['settings'] ?? false)) {
            return true;
        }
        return false;
    }
}
