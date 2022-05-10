<?php declare(strict_types=1);
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
 * @author   Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup  ServicesCalendar
 */
class ilCalendarAppointmentColors
{
    protected static array $colors = array(
        'crs' => array(
            "#ADD8E6",
            "#BFEFFF",
            "#B2DFEE",
            "#9AC0CD",
            "#475A5F",
            "#E0FFFF",
            "#D1EEEE",
            "#B4CDCD",
            "#7A8B8B",
            "#87CEFA",
            "#B0E2FF",
            "#A4D3EE",
            "#8DB6CD",
            "#607B8B",
            "#B0C4DE",
            "#CAE1FF",
            "#BCD2EE",
            "#A2B5CD"
        ),
        'grp' => array(
            "#EEDD82",
            "#FFEC8B",
            "#EEDC82",
            "#CDBE70",
            "#8B814C",
            "#FAFAD2",
            "#FFFFE0",
            "#FFF8DC",
            "#EEEED1",
            "#CDCDB4"
        ),
        'sess' => array(
            "#C1FFC1",
            "#B4EEB4",
            "#98FB98",
            "#90EE90"
        ),
        'exc' => array(
            "#BC6F16",
            "#BA7832",
            "#B78B4D",
            "#B59365"
        ),
        'tals' => array(
            "#BC6F16",
            "#BA7832",
            "#B78B4D",
            "#B59365"
        ),
        'etal' => array(
            "#BC6F16",
            "#BA7832",
            "#B78B4D",
            "#B59365"
        )
    );

    protected ilDBInterface $db;
    protected ilCalendarCategories $categories;

    private array $appointment_colors = [];
    private array $cat_substitutions_colors = [];
    private array $cat_substitutions = [];
    private array $cat_app_ass = [];

    public function __construct($a_user_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->categories = ilCalendarCategories::_getInstance();
        $this->read();
    }

    /**
     * get color by appointment
     * @access public
     * @param int calendar appointment id
     * @return
     */
    public function getColorByAppointment($a_cal_id)
    {
        $cat_id = $this->cat_app_ass[$a_cal_id];
        $cat_id = $this->cat_substitutions[$cat_id];
        #21078
        if (isset($this->appointment_colors[$cat_id])) {
            return $this->appointment_colors[$cat_id];
        } elseif (isset($this->cat_substitutions_colors[$cat_id])) {
            return $this->cat_substitutions_colors[$cat_id];
        } else {
            return 'red';
        }
    }

    private function read()
    {
        // Store assignment of subitem categories
        foreach ($this->categories->getCategoriesInfo() as $c_data) {
            if (isset($c_data['subitem_ids']) and count($c_data['subitem_ids'])) {
                foreach ($c_data['subitem_ids'] as $sub_item_id) {
                    $this->cat_substitutions[$sub_item_id] = $c_data['cat_id'];
                }
            }
            $this->cat_substitutions[$c_data['cat_id']] = $c_data['cat_id'];
            #21078
            $this->cat_substitutions_colors[$c_data['cat_id']] = $c_data['color'];
        }

        $query = "SELECT cat.cat_id,cat.color, ass.cal_id  FROM cal_categories cat " .
            "JOIN cal_cat_assignments ass ON cat.cat_id = ass.cat_id " .
            "WHERE " . $this->db->in('cat.cat_id', $this->categories->getCategories(true), false, 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->appointment_colors[$row->cat_id] = $row->color;
            $this->cat_app_ass[$row->cal_id] = $row->cat_id;
        }
    }

    public static function _getRandomColorByType(string $a_type) : string
    {
        $random = new \ilRandom();
        return self::$colors[$a_type][$random->int(0, count(self::$colors[$a_type]) - 1)];
    }

    /**
     * get selectable colors
     */
    public static function _getColorsByType(string $a_type) : array
    {
        return self::$colors[$a_type];
    }
}
