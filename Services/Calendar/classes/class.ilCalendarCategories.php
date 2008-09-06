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
include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');

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
	const MODE_PERSONAL_DESKTOP = 1;
	const MODE_REPOSITORY = 2;
	
	protected static $instance = null;
	
	protected $db;
	
	protected $user_id;
	
	protected $categories = array();
	protected $categories_info = array();
	
	protected $root_ref_id = 0;
	protected $root_obj_id = 0;


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
	 * check if user is owner of a category
	 *
	 * @access public
	 * @param int usr_id
	 * @param int cal_id
	 * @return bool
	 * @static
	 */
	public static function _isOwner($a_usr_id,$a_cal_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM cal_categories ".
			"WHERE cat_id = ".$ilDB->quote($a_cal_id)." ".
			"AND obj_id = ".$ilDB->quote($a_usr_id)." ".
			"AND type = ".$ilDB->quote(ilCalendarCategory::TYPE_USR)." ";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * initialize visible categories
	 *
	 * @access public
	 * @param int mode 
	 * @param int ref_id of root node
	 * @return
	 */
	public function initialize($a_mode,$a_source_ref_id = 0)
	{
		switch($a_mode)
		{
			case self::MODE_PERSONAL_DESKTOP:
				$this->readPDCalendars();
				break;
				
			case self::MODE_REPOSITORY:
				$this->root_ref_id = $a_source_ref_id;
				$this->root_obj_id = ilObject::_lookupObjId($this->root_ref_id);
				$this->readReposCalendars();
				break;
		}
		
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
			if($info['obj_type'] == 'sess')
			{
				continue;
			}
			if($info['type'] == ilCalendarCategory::TYPE_USR and $info['editable'])
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
	protected function readPDCalendars()
	{
		global $rbacsystem;
		
		
		$this->readPublicCalendars();
		$this->readPrivateCalendars();
		
		include_once('./Services/Membership/classes/class.ilParticipants.php');
		$this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id,'crs'));
		$this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id,'grp'));
	}

	/**
	 * Read available repository calendars 
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function readReposCalendars()
	{
		global $ilAccess,$tree;
		
		$this->readPublicCalendars();
		$this->readPrivateCalendars();
		
		$query = "SELECT ref_id,obd.obj_id AS obj_id FROM tree AS t1 ".
			"JOIN object_reference AS obr ON t1.child = obr.ref_id ".
			"JOIN object_data AS obd ON obd.obj_id = obr.obj_id ".
			"WHERE t1.lft >= (SELECT lft FROM tree WHERE child = ".$this->db->quote($this->root_ref_id)." ) ".
			"AND t1.lft <= (SELECT rgt FROM tree WHERE child = ".$this->db->quote($this->root_ref_id)." ) ".
			"AND type IN('crs','grp','sess') ".
			"AND tree = 1";
			
		$res = $this->db->query($query);
		$obj_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($tree->isDeleted($row->ref_id))
			{
				continue;
			}
			
			if($ilAccess->checkAccess('read','',$row->ref_id))
			{
				$obj_ids[] = $row->obj_id;
			}
		}
		$this->readSelectedCategories($obj_ids);
		
		
	}
	
	/**
	 * Read public calendars
	 *
	 * @access protected
	 * @return
	 */
	protected function readPublicCalendars()
	{
		global $rbacsystem,$ilAccess;
		
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
		return true;
	}
	
	/**
	 * Read private calendars
	 *
	 * @access protected
	 * @return
	 */
	protected function readPrivateCalendars()
	{
		global $ilUser;

		// First read private calendars of user
		$query = "SELECT cat_id FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_USR)." ".
			"AND obj_id = ".$this->db->quote($this->user_id)." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cat_ids[] = $row->cat_id;
		}
		
		// Read shared calendars
		include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
		if(!$cat_ids = array_merge((array) $cat_ids,ilCalendarSharedStatus::getAcceptedCalendars($ilUser->getId())))
		{
			return true;
		}
		
		
		// user categories
		$query = "SELECT * FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_USR)." ".
			"AND cat_id IN (".implode(',',ilUtil::quoteArray($cat_ids)).') '.
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
			$this->categories_info[$row->cat_id]['editable'] = $row->obj_id == $ilUser->getId();
		}
	}
	


	/**
	 * read selected categories
	 *
	 * @access protected
	 * @return
	 */
	protected function readSelectedCategories($a_obj_ids)
	{
		global $ilAccess,$tree;
		
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
			$editable = false;
			$exists = false;
			foreach(ilObject::_getAllReferences($row->obj_id) as $ref_id)
			{
				if($tree->isDeleted($ref_id))
				{
					continue;
				}
				if($ilAccess->checkAccess('write','',$ref_id))
				{
					$exists = true;
					$editable = true;
					break;
				}
				elseif($ilAccess->checkAccess('read','',$ref_id))
				{
					$exists = true;
				}
			}
			if(!$exists)
			{
				continue;
			}
			$this->categories_info[$row->cat_id]['editable'] = $editable;
			
			$this->categories[] = $row->cat_id;
			$this->categories_info[$row->cat_id]['obj_id'] = $row->obj_id;
			$this->categories_info[$row->cat_id]['cat_id'] = $row->cat_id;
			$this->categories_info[$row->cat_id]['color'] = $row->color;
			$this->categories_info[$row->cat_id]['title'] = ilObject::_lookupTitle($row->obj_id);
			$this->categories_info[$row->cat_id]['obj_type'] = ilObject::_lookupType($row->obj_id);
			$this->categories_info[$row->cat_id]['type'] = $row->type;
		}
			
	}
	
}
?>