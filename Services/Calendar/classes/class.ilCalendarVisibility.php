<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

/**
* Stores selection of hidden calendars for a specific user
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarVisibility
{
    const HIDDEN = 0;
    const VISIBLE = 1;

    /**
     * @var array
     */
    protected static $instances = array();

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var array hidden cal cats
     */
    protected $hidden = array();

    /**
     * @var array visible cal cats
     */
    protected $visible = array();

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     *
     * @param int $a_user_id user id
     * @param int $a_ref_id object ref id
     * @param int user id
     */
    private function __construct($a_user_id, $a_ref_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->user_id = $a_user_id;
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        $this->read();
    }
    
    /**
     * get instance by user id
     *
     * @param int $a_user_id user id
     * @param int $a_ref_id object ref id
     * @return ilCalendarVisibility
     */
    public static function _getInstanceByUserId($a_user_id, $a_ref_id = 0)
    {
        if (!isset(self::$instances[$a_user_id][$a_ref_id])) {
            self::$instances[$a_user_id][$a_ref_id] = new ilCalendarVisibility($a_user_id, $a_ref_id);
        }
        return self::$instances[$a_user_id][$a_ref_id];
    }
    
    /**
     * delete by category
     *
     * @access public
     * @param int category id
     * @static
     */
    public static function _deleteCategories($a_cat_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "DELETE FROM cal_cat_visibility " .
            "WHERE cat_id = " . $ilDB->quote($a_cat_id, 'integer') . " ";
        $ilDB->manipulate($query);
    }
    
    /**
     * Delete by user
     *
     * @access public
     * @param int $a_user_id user_id
     * @static
     */
    public static function _deleteUser($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM cal_cat_visibility " .
            "WHERE user_id = " . $ilDB->quote($a_user_id, 'integer') . " ";
        $ilDB->manipulate($query);
    }
    
    /**
     * Filter hidden categories (and hidden subitem categories) from category array
     * @param object $categories
     * @param object $category_info
     * @return
     */
    public function filterHidden($categories, $category_info)
    {
        $hidden = array();
        foreach ($category_info as $cat_id => $info) {
            if ($this->isHidden($cat_id, $info)) {
                $hidden = array_merge((array) $hidden, (array) $info['subitem_ids'], array($cat_id));
            }
        }
        return array_diff((array) $categories, $hidden);
    }
    
    /**
     * Check if category is hidden.
     * @param object $a_cat_id
     * @return
     */
    protected function isHidden($a_cat_id, $info)
    {
        // personal desktop
        if ($this->obj_id == 0) {
            return in_array($a_cat_id, $this->hidden);
        }

        // crs/grp, always show current object and objects that have been selected due to
        // current container ref id
        if ($info["type"] == ilCalendarCategory::TYPE_OBJ && ($info["obj_id"] == $this->obj_id
            || $info["source_ref_id"] == $this->ref_id)) {
            return false;
        }

        return !in_array($a_cat_id, $this->visible);
    }
    
    /**
     * check whether an appoinment is visible or not
     *
     * @access public
     * @param
     * @return
     */
    public function isAppointmentVisible($a_cal_id)
    {
        include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
        
        foreach (ilCalendarCategoryAssignments::_lookupCategories($a_cal_id) as $cat_id) {
            if (in_array($cat_id, $this->hidden)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * get hidden categories
     *
     * @access public
     * @return array array of category ids
     */
    public function getHidden()
    {
        return $this->hidden ? $this->hidden : array();
    }

    /**
     * get visible categories
     *
     * @access public
     * @return array array of category ids
     */
    public function getVisible()
    {
        return $this->visible ? $this->visible : array();
    }
    
    /**
     * hide selected
     *
     * @access public
     * @param array array of hidden categories
     * @return bool
     */
    public function hideSelected($a_hidden)
    {
        $this->hidden = $a_hidden;
        return true;
    }

    /**
     * Show selected
     *
     * @access public
     * @param array array of visible categories
     * @return bool
     */
    public function showSelected($a_visible)
    {
        $this->visible = $a_visible;
        return true;
    }

    /**
     * save hidden selection
     *
     * @access public
     * @return bool
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $this->delete();
        foreach ($this->hidden as $hidden) {
            $query = "INSERT INTO cal_cat_visibility (user_id, cat_id, obj_id, visible) " .
                "VALUES ( " .
                $this->db->quote($this->user_id, 'integer') . ", " .
                $this->db->quote($hidden, 'integer') . ", " .
                $this->db->quote($this->obj_id, 'integer') . ", " .
                $this->db->quote(self::HIDDEN, 'integer') .
                ")";
            $ilDB->manipulate($query);
        }
        foreach ($this->visible as $visible) {
            $query = "INSERT INTO cal_cat_visibility (user_id, cat_id, obj_id, visible) " .
                "VALUES ( " .
                $this->db->quote($this->user_id, 'integer') . ", " .
                $this->db->quote($visible, 'integer') . ", " .
                $this->db->quote($this->obj_id, 'integer') . ", " .
                $this->db->quote(self::VISIBLE, 'integer') .
                ")";
            $ilDB->manipulate($query);
        }
        return true;
    }
    
    /**
     * delete
     *
     * @access public
     * @param int $a_cat_id cat id (if empty all categories are deleted)
     * @return bool
     */
    public function delete($a_cat_id = null)
    {
        if ($a_cat_id) {
            $query = "DELETE FROM cal_cat_visibility " .
                "WHERE user_id = " . $this->db->quote($this->user_id, 'integer') . " " .
                "AND obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
                "AND cat_id = " . $this->db->quote($a_cat_id, 'integer') . " ";
        } else {
            $query = "DELETE FROM cal_cat_visibility " .
                "WHERE user_id = " . $this->db->quote($this->user_id, 'integer') . " " .
                "AND obj_id = " . $this->db->quote($this->obj_id, 'integer');
        }
        $this->db->manipulate($query);
        return true;
    }
    
    /**
     * read user selection
     *
     * @access protected
     * @return bool
     */
    protected function read()
    {
        $query = "SELECT * FROM cal_cat_visibility " .
            "WHERE user_id = " . $this->db->quote($this->user_id, 'integer') . " " .
            " AND obj_id = " . $this->db->quote($this->obj_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->visible == self::HIDDEN) {
                $this->hidden[] = $row->cat_id;
            }
            if ($row->visible == self::VISIBLE) {
                $this->visible[] = $row->cat_id;
            }
        }
        return true;
    }

    /**
     * Force visibility
     *
     * @param
     */
    public function forceVisibility($a_cat_id)
    {
        if (($key = array_search($a_cat_id, $this->hidden)) !== false) {
            unset($this->hidden[$key]);
        }
        if (!in_array($a_cat_id, $this->visible)) {
            $this->visible[] = $a_cat_id;
        }
    }
}
