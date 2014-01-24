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
class ilCalendarHidden
{
	protected static $instances = array();
	
	protected $user_id;
	protected $hidden = array();
	
	protected $db;

	/**
	 * Singleton constructor
	 *
	 * @access private
	 * @param int user id
	 */
	private function __construct($a_user_id)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->user_id = $a_user_id;
		$this->read();
	}
	
	/**
	 * get instance by user id
	 *
	 * @access public
	 * @param int user id
	 * @return object 
	 * @static
	 */
	public static function _getInstanceByUserId($a_user_id)
	{
		if(isset(self::$instances[$a_user_id]))
		{
			return self::$instances[$a_user_id];
		}
		return self::$instances[$a_user_id] = new ilCalendarHidden($a_user_id); 
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
		global $ilDB;
		
		$query = "DELETE FROM cal_categories_hidden ".
			"WHERE cat_id = ".$ilDB->quote($a_cat_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * Delete by user
	 *
	 * @access public
	 * @param int user_id
	 * @return
	 * @static
	 */
	public static function _deleteUser($a_user_id)
	{
		global $ilDB;
		
		$query = "DELETE FROM cal_categories_hidden ".
			"WHERE user_id = ".$ilDB->quote($a_user_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
	}
	
	/**
	 * Filter hidden categories (and hidden subitem categories) from category array
	 * @param object $categories
	 * @param object $category_info
	 * @return 
	 */
	public function filterHidden($categories,$category_info)
	{
		$hidden = array();
		foreach($category_info as $cat_id => $info)
		{
			if($this->isHidden($cat_id))
			{
				$hidden = array_merge((array) $hidden,(array) $info['subitem_ids'],array($cat_id));
			}
		}
		return array_diff((array) $categories, $hidden);
	}
	
	/**
	 * Check if category is hidden.
	 * @param object $a_cat_id
	 * @return 
	 */
	public function isHidden($a_cat_id)
	{
		return in_array($a_cat_id, $this->hidden);
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
		
		foreach(ilCalendarCategoryAssignments::_lookupCategories($a_cal_id) as $cat_id)
		{
			if(in_array($cat_id,$this->hidden))
			{
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
	 * save hidden selection
	 *
	 * @access public
	 * @return bool
	 */
	public function save()
	{
		global $ilDB;
		
		$this->delete();
		foreach($this->hidden as $hidden)
		{
			$query = "INSERT INTO cal_categories_hidden (user_id,cat_id) ".
				"VALUES ( ".
				$this->db->quote($this->user_id ,'integer').", ".
				$this->db->quote($hidden ,'integer')." ".
				")";
			$res = $ilDB->manipulate($query);
		}
		return true;
	}
	
	/**
	 * delete 
	 *
	 * @access public
	 * @param int cat id (if empty all categories are deleted)
	 * @return bool
	 */
	public function delete($a_cat_id = null)
	{
		global $ilDB;
		
		if($a_cat_id)
		{
			$query = "DELETE FROM cal_categories_hidden ".
				"WHERE user_id = ".$this->db->quote($this->user_id ,'integer')." ".
				"AND cat_id = ".$this->db->quote($a_cat_id ,'integer')." ";
		}
		else
		{
			$query = "DELETE FROM cal_categories_hidden ".
				"WHERE user_id = ".$this->db->quote($this->user_id ,'integer')." ";
		}
		$res = $ilDB->manipulate($query);
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
		global $ilDB;
		
		$query = "SELECT * FROM cal_categories_hidden ".
			"WHERE user_id = ".$this->db->quote($this->user_id ,'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->hidden[] = $row->cat_id;
		}
		return true;
	}
}
?>