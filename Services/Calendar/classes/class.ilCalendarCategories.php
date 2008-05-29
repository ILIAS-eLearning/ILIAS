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

include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');

/**
* class for calendar categories
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarCategories
{
	protected static $instance = null;
	
	protected $db;
	
	protected $user_id;
	
	protected $categories = array();
	protected $categories_info = array();


	/**
	 * Singleton instance
	 *
	 * @access protected
	 * @param int $a_usr_id user id
	 * @return
	 */
	protected function __construct($a_usr_id = 0)
	{
		global $ilUser,$ilDB;
		
		$this->user_id = $a_usr_id;
		if(!$this->user_id)
		{
			$this->user_id = $ilUser->getId();
		}
		$this->db = $ilDB;
		$this->read();
	}

	/**
	 * get singleton instance
	 *
	 * @access public
	 * @param int $a_usr_id user id
	 * @return
	 * @static
	 */
	public static function _getInstance($a_usr_id = 0)
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilCalendarCategories($a_usr_id);
	}
	
	/**
	 * lookup category by obj_id
	 *
	 * @access public
	 * @param int obj_id
	 * @return int cat_id
	 * @static
	 */
	public static function _lookupCategoryIdByObjId($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT cat_id FROM cal_categories  ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND type = ".$ilDB->quote(ilCalendarCategory::TYPE_OBJ)." ";
			
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->cat_id;
		}
		return 0;
	}
	
	/**
	 * 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getCategoryInfo($a_cat_id)
	{
		return $this->categories_info[$a_cat_id];
	}
	
	
	/**
	 * get categories
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getCategoriesInfo()
	{
		return $this->categories_info ? $this->categories_info : array();
	}
	
	/**
	 * get categories
	 *
	 * @access public
	 * @return
	 */
	public function getCategories()
	{
		return $this->categories ? $this->categories : array();
	}
	
	/**
	 * prepare categories of users for selection
	 *
	 * @access public
	 * @param int user id
	 * @return
	 */
	public function prepareCategoriesOfUserForSelection()
	{
		global $lng;
		
		$has_personal_calendar = false;
		foreach($this->categories_info as $info)
		{
			if($info['type'] == ilCalendarCategory::TYPE_USR)
			{
				$has_personal_calendar = true;
			}

			if($info['editable'])
			{
				$cats[$info['cat_id']] = $info['title'];
			}
		}
		// If there 
		if(!$has_personal_calendar)
		{
			$cats[0] = $lng->txt('cal_default_calendar'); 
		}
		return $cats ? $cats : array();
	}
	
	/**
	 * check if category is editable
	 *
	 * @access public
	 * @param int $a_cat_id category id
	 * @return
	 */
	public function isEditable($a_cat_id)
	{
		return isset($this->categories_info[$a_cat_id]['editable']) and $this->categories_info[$a_cat_id]['editable'];
	}
	
	
	
	/**
	 * Read categories of user
	 *
	 * @access protected
	 * @param
	 * @return void
	 */
	protected function read()
	{
		global $rbacsystem;
		
		// global categories
		$query = "SELECT * FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_GLOBAL)." ".
			"ORDER BY title ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->categories[] = $row->cat_id;
			$this->categories_info[$row->cat_id]['obj_id'] = $row->obj_id;
			$this->categories_info[$row->cat_id]['cat_id'] = $row->cat_id;
			$this->categories_info[$row->cat_id]['title'] = $row->title;
			$this->categories_info[$row->cat_id]['color'] = $row->color;
			$this->categories_info[$row->cat_id]['type'] = $row->type;
			$this->categories_info[$row->cat_id]['editable'] = $rbacsystem->checkAccess('edit_event',ilCalendarSettings::_getInstance()->getCalendarSettingsId());
		}

		// user categories
		$query = "SELECT * FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_USR)." ".
			"AND obj_id = ".$this->db->quote($this->user_id)." ".
			"ORDER BY title ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->categories[] = $row->cat_id;
			$this->categories_info[$row->cat_id]['obj_id'] = $row->obj_id;
			$this->categories_info[$row->cat_id]['cat_id'] = $row->cat_id;
			$this->categories_info[$row->cat_id]['title'] = $row->title;
			$this->categories_info[$row->cat_id]['color'] = $row->color;
			$this->categories_info[$row->cat_id]['type'] = $row->type;
			$this->categories_info[$row->cat_id]['editable'] = true;
		}
		
		include_once('./Services/Membership/classes/class.ilParticipants.php');
		$this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id,'crs'));
		$this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id,'grp'));
		
		
	}

	/**
	 * read selected categories
	 *
	 * @access protected
	 * @return
	 */
	protected function readSelectedCategories($a_obj_ids)
	{
		global $ilAccess;
		
		if(!count($a_obj_ids))
		{
			return true;
		}

		$query = "SELECT * FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_OBJ)." ".
			"AND obj_id IN (".implode(',',ilUtil::quoteArray($a_obj_ids)).') ';
		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->categories[] = $row->cat_id;
			$this->categories_info[$row->cat_id]['obj_id'] = $row->obj_id;
			$this->categories_info[$row->cat_id]['cat_id'] = $row->cat_id;
			$this->categories_info[$row->cat_id]['color'] = $row->color;
			$this->categories_info[$row->cat_id]['title'] = ilObject::_lookupTitle($row->obj_id);
			$this->categories_info[$row->cat_id]['type'] = $row->type;
	
			$this->categories_info[$row->cat_id]['editable'] = false;
			foreach(ilObject::_getAllReferences($row->obj_id) as $ref_id)
			{
				if($ilAccess->checkAccess('write','',$ref_id))
				{
					$this->categories_info[$row->cat_id]['editable'] = true;
					break;
				}
			}
		}
	}
	
}
?>