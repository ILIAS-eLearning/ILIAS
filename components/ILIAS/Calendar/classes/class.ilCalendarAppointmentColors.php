<?php

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

declare(strict_types=1);
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

    public static function _getRandomColorByType(string $a_type): string
    {
        $random = new \ilRandom();
        return self::$colors[$a_type][$random->int(0, count(self::$colors[$a_type]) - 1)];
    }

    /**
     * get selectable colors
     */
    public static function _getColorsByType(string $a_type): array
    {
        return self::$colors[$a_type];
    }
}
