<?php

declare(strict_types=1);
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
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarVisibility
{
    public const HIDDEN = 0;
    public const VISIBLE = 1;

    protected static array $instances = array();
    protected int $user_id = 0;
    protected int $ref_id = 0;
    protected int $obj_id = 0;
    protected array $hidden = array();
    protected array $visible = array();

    protected ilDBInterface $db;

    private function __construct(int $a_user_id, int $a_ref_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->user_id = $a_user_id;
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        $this->read();
    }

    public static function _getInstanceByUserId(int $a_user_id, int $a_ref_id = 0): ilCalendarVisibility
    {
        if (!isset(self::$instances[$a_user_id][$a_ref_id])) {
            self::$instances[$a_user_id][$a_ref_id] = new ilCalendarVisibility($a_user_id, $a_ref_id);
        }
        return self::$instances[$a_user_id][$a_ref_id];
    }

    public static function _deleteCategories(int $a_cat_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM cal_cat_visibility " .
            "WHERE cat_id = " . $ilDB->quote($a_cat_id, 'integer') . " ";
        $ilDB->manipulate($query);
    }

    public static function _deleteUser(int $a_user_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM cal_cat_visibility " .
            "WHERE user_id = " . $ilDB->quote($a_user_id, 'integer') . " ";
        $ilDB->manipulate($query);
    }

    /**
     * Filter hidden categories (and hidden subitem categories) from category array
     */
    public function filterHidden(array $categories, array $category_info): array
    {
        $hidden = array();
        foreach ($category_info as $cat_id => $info) {
            $subitem_ids = [];
            if (array_key_exists('subitem_ids', $info) && is_array($info['subitem_ids'])) {
                $subitem_ids = $info['subitem_ids'];
            }

            if ($this->isHidden($cat_id, $info)) {
                $hidden = array_merge((array) $hidden, $subitem_ids, array($cat_id));
            }
        }
        return array_diff($categories, $hidden);
    }

    protected function isHidden(int $a_cat_id, array $info): bool
    {
        // personal desktop
        if ($this->obj_id == 0) {
            return in_array($a_cat_id, $this->hidden);
        }

        // crs/grp, always show current object and objects that have been selected due to
        // current container ref id
        if (
            $info["type"] == ilCalendarCategory::TYPE_OBJ &&
            ($info["obj_id"] == $this->obj_id || $info["source_ref_id"] == $this->ref_id)
        ) {
            return false;
        }
        return !in_array($a_cat_id, $this->visible);
    }

    public function isAppointmentVisible(int $a_cal_id): bool
    {
        foreach (ilCalendarCategoryAssignments::_lookupCategories($a_cal_id) as $cat_id) {
            if (in_array($cat_id, $this->hidden)) {
                return true;
            }
        }
        return false;
    }

    public function getHidden(): array
    {
        return $this->hidden;
    }

    public function getVisible(): array
    {
        return $this->visible;
    }

    public function hideSelected(array $a_hidden): void
    {
        $this->hidden = $a_hidden;
    }

    public function showSelected(array $a_visible): void
    {
        $this->visible = $a_visible;
    }

    public function save(): void
    {
        $this->delete();
        foreach ($this->hidden as $hidden) {
            $query = "INSERT INTO cal_cat_visibility (user_id, cat_id, obj_id, visible) " .
                "VALUES ( " .
                $this->db->quote($this->user_id, 'integer') . ", " .
                $this->db->quote($hidden, 'integer') . ", " .
                $this->db->quote($this->obj_id, 'integer') . ", " .
                $this->db->quote(self::HIDDEN, 'integer') .
                ")";
            $this->db->manipulate($query);
        }
        foreach ($this->visible as $visible) {
            $query = "INSERT INTO cal_cat_visibility (user_id, cat_id, obj_id, visible) " .
                "VALUES ( " .
                $this->db->quote($this->user_id, 'integer') . ", " .
                $this->db->quote($visible, 'integer') . ", " .
                $this->db->quote($this->obj_id, 'integer') . ", " .
                $this->db->quote(self::VISIBLE, 'integer') .
                ")";
            $this->db->manipulate($query);
        }
    }

    public function delete(int $a_cat_id = null): void
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
    }

    protected function read(): void
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
    }

    public function forceVisibility(int $a_cat_id): void
    {
        if (($key = array_search($a_cat_id, $this->hidden)) !== false) {
            unset($this->hidden[$key]);
        }
        if (!in_array($a_cat_id, $this->visible)) {
            $this->visible[] = $a_cat_id;
        }
    }
}
