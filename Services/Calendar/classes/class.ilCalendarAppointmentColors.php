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

include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup  ServicesCalendar
*/

class ilCalendarAppointmentColors
{
	protected static $colors = array('crs' => array(
										"#ADD8E6",	 
										"#BFEFFF",
										"#B2DFEE",
										"#9AC0CD",
										"#68838B",
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
										"#A2B5CD"),
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
										"#CDCDB4"),
									'sess' => array(
										"#C1FFC1",
										"#B4EEB4",
										"#98FB98",
										"#90EE90"),
									'exc' => array(
										"#BC6F16",
										"#BA7832",
										"#B78B4D",
										"#B59365"));
																				
									
	
	protected $db;
	protected $user_id;
	protected $appointment_colors;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int user_id
	 * @return
	 */
	public function __construct($a_user_id)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		
		$this->user_id = $a_user_id;
		
		$this->categories = ilCalendarCategories::_getInstance();
		$this->read();
	}
	
	/**
	 * get color by appointment
	 *
	 * @access public
	 * @param int calendar appointment id
	 * @return
	 */
	public function getColorByAppointment($a_cal_id)
	{
		$cat_id = $this->cat_app_ass[$a_cal_id];
		$cat_id = $this->cat_substitutions[$cat_id];
		
		return isset($this->appointment_colors[$cat_id]) ? $this->appointment_colors[$cat_id] : 'red';
	}
	
	/**
	 * read
	 *
	 * @access private
	 * @param
	 * @return
	 */
	private function read()
	{
		global $ilDB;

		// Store assignment of subitem categories
		foreach($this->categories->getCategoriesInfo() as $c_data)
		{
			if(isset($c_data['subitem_ids']) and count($c_data['subitem_ids']))
			{
				foreach($c_data['subitem_ids'] as $sub_item_id)
				{
					$this->cat_substitutions[$sub_item_id] = $c_data['cat_id'];
				}
				
			}
			$this->cat_substitutions[$c_data['cat_id']] = $c_data['cat_id'];
		}
		
		$query = "SELECT cat.cat_id,cat.color, ass.cal_id  FROM cal_categories cat ".
			"JOIN cal_cat_assignments ass ON cat.cat_id = ass.cat_id ".
			"WHERE ".$ilDB->in('cat.cat_id',$this->categories->getCategories(true),false,'integer');

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->appointment_colors[$row->cat_id] = $row->color;
			$this->cat_app_ass[$row->cal_id] = $row->cat_id;
		}
	}
	
	/**
	 * get random color entry for type
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function _getRandomColorByType($a_type)
	{
		return self::$colors[$a_type][rand(0,count(self::$colors[$a_type]) - 1)];
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function dumpColors()
	{
		foreach(self::$colors['grp'] as $color)
		{
			echo '<font color="'.$color.'">HALLO</font><br/>';
		}
		foreach(self::$colors['crs'] as $color)
		{
			echo '<font color="'.$color.'">HALLO</font><br/>';
		}
	}

	/**
	 * get selectable colors
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function _getColorsByType($a_type)
	{
		return self::$colors[$a_type];
	}
}
?>