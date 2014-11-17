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
include_once './Services/Calendar/classes/class.ilCalendarCache.php';

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
	const MODE_REPOSITORY = 2;
	const MODE_REMOTE_ACCESS = 3;
	const MODE_PERSONAL_DESKTOP_MEMBERSHIP = 4;
	const MODE_PERSONAL_DESKTOP_ITEMS = 5; 
	const MODE_MANAGE = 6;
	const MODE_CONSULTATION = 7;
	const MODE_PORTFOLIO_CONSULTATION = 8;
	const MODE_REMOTE_SELECTED = 9;
	
	protected static $instance = null;
	
	protected $db;
	
	protected $user_id;
	
	protected $mode = 0;
	
	protected $categories = array();
	protected $categories_info = array();
	protected $subitem_categories = array();
	
	protected $root_ref_id = 0;
	protected $root_obj_id = 0;


	protected $ch_user_id = 0;
	protected $target_ref_id = 0;
	
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
	 * @return ilCalendarCategories
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
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND type = ".$ilDB->quote(ilCalendarCategory::TYPE_OBJ,'integer')." ";
			
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
			"WHERE cat_id = ".$ilDB->quote($a_cal_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND type = ".$ilDB->quote(ilCalendarCategory::TYPE_USR ,'integer')." ";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * Delete cache (add remove desktop item)
	 * @param object $a_usr_id
	 * @return 
	 */
	public static function deletePDItemsCache($a_usr_id)
	{
		ilCalendarCache::getInstance()->deleteByAdditionalKeys(
			$a_usr_id,
			self::MODE_PERSONAL_DESKTOP_ITEMS,
			'categories'
		);
	}
	
	/**
	 * Delete cache
	 * @param object $a_usr_id
	 * @return 
	 */
	public static function deleteRepositoryCache($a_usr_id)
	{
		ilCalendarCache::getInstance()->deleteByAdditionalKeys(
			$a_usr_id,
			self::MODE_REPOSITORY,
			'categories'
		);
		
	}
	

	/**
	 * Serialize categories 
	 * @return 
	 */
	protected function sleep()
	{
		return serialize(
			array(
				'categories'		=> $this->categories,
				'categories_info'	=> $this->categories_info,
				'subitem_categories'=> $this->subitem_categories
			)
		);
	}
	
	/**
	 * Load from serialize string
	 * @param string serialize categories
	 * @return 
	 */
	protected function wakeup($a_ser)
	{
		$info = unserialize($a_ser);
		
		$this->categories = $info['categories'];
		$this->categories_info = $info['categories_info'];
		$this->subitem_categories = $info['subitem_categories'];
	}
	
	/**
	 * Set ch user id
	 * @param int $a_user_id
	 */
	public function setCHUserId($a_user_id)
	{
		$this->ch_user_id = $a_user_id;
	}
	
	
	/**
	 * Get ch user id
	 * @return type
	 */
	public function getCHUserId()
	{
		return $this->ch_user_id;
	}
	
	protected function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}
	
	public function getMode()
	{
		return $this->mode;
	}
	
	protected function setTargetRefId($a_ref_id)
	{
		$this->target_ref_id = $a_ref_id;
	}
	
	public function getTargetRefId()
	{
		return $this->target_ref_id;
	}

	/**
	 * initialize visible categories
	 *
	 * @access public
	 * @param int mode 
	 * @param int ref_id of root node
	 * @return
	 */
	public function initialize($a_mode,$a_source_ref_id = 0,$a_use_cache = false)
	{
		$this->setMode($a_mode);
		if($a_use_cache)
		{
			// Read categories from cache
			if($cats = ilCalendarCache::getInstance()->getEntry($this->user_id.':'.$a_mode.':categories:'.(int) $a_source_ref_id))
			{
				if($this->getMode() != self::MODE_CONSULTATION &&
					$this->getMode() != self::MODE_PORTFOLIO_CONSULTATION)
				{
					$this->wakeup($cats);
					return;
				}
			}
		}
		
		
		switch($this->getMode())
		{
			case self::MODE_REMOTE_ACCESS:
				include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
				if(ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
				{
					$this->readPDCalendars();
				}
				else
				{
					$this->readSelectedItemCalendars();			
				}
				break;
				
			case self::MODE_REMOTE_SELECTED:
				$this->readSelectedCalendar($a_source_ref_id);
				break;
				
			case self::MODE_PERSONAL_DESKTOP_MEMBERSHIP:
				$this->readPDCalendars();
				break;
				
			case self::MODE_PERSONAL_DESKTOP_ITEMS:
				$this->readSelectedItemCalendars();
				break;
				
			case self::MODE_REPOSITORY:
				$this->root_ref_id = $a_source_ref_id;
				$this->root_obj_id = ilObject::_lookupObjId($this->root_ref_id);
				$this->readReposCalendars();
				break;

			case self::MODE_MANAGE:
				$this->readPDCalendars();
				$this->readSelectedItemCalendars();
				break;

			case self::MODE_CONSULTATION:
				#$this->readPrivateCalendars();
				$this->setTargetRefId($a_source_ref_id);
				$this->readConsultationHoursCalendar($a_source_ref_id);
				break;
			
			case self::MODE_PORTFOLIO_CONSULTATION:
				$this->readConsultationHoursCalendar();
				break;
		}
		
		if($a_use_cache)
		{
			// Store in cache
			ilCalendarCache::getInstance()->storeEntry(
				$this->user_id.':'.$a_mode.':categories:'.(int) $a_source_ref_id,
				$this->sleep(),
				$this->user_id,
				$a_mode,
				'categories'
			);
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
		if(isset($this->categories_info[$a_cat_id]))
		{
			return $this->categories_info[$a_cat_id];
		}
		
		if(in_array($a_cat_id,(array) $this->subitem_categories))
		{
			foreach($this->categories as $cat_id)
			{
				if(in_array($a_cat_id,$this->categories_info[$cat_id]['subitem_ids']))
				{
					return $this->categories_info[$cat_id];
				}
			}
		}
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
	 * @param $a_include_subitems include subitem calendars
	 * @access public
	 * @return
	 */
	public function getCategories($a_include_subitem_calendars = false)
	{
		if($a_include_subitem_calendars)
		{
			return array_merge((array) $this->categories, (array) $this->subitem_categories);
		}
		
		return $this->categories ? $this->categories : array();
	}
	
	/**
	 * get subitem categories for a specific category
	 *  
	 * @param int $a_category_id Id of category in question
	 * @return array Array of category ids
	 */
	public function getSubitemCategories($a_cat_id)
	{
		if(!isset($this->categories_info[$a_cat_id]['subitem_ids']))
		{
			return array($a_cat_id);
		}
		return array_merge((array) $this->categories_info[$a_cat_id]['subitem_ids'],array($a_cat_id));
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
			if($info['obj_type'] == 'sess' || $info['obj_type'] == 'exc')
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
	 * Get all calendars that allow send of notifications
	 * (Editable and course group calendars)
	 * @return 
	 */
	public function getNotificationCalendars()
	{
		$not = array();
		foreach($this->categories_info as $info)
		{
			if($info['type'] == ilCalendarCategory::TYPE_OBJ and $info['editable'] == true)
			{
				if(ilObject::_lookupType($info['obj_id']) == 'crs' or ilObject::_lookupType($info['obj_id']) == 'grp')
				{
					$not[] = $info['cat_id'];
				}
			}
		}
		return $not;		
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
	 * check if category is visible
	 *
	 * @access public
	 * @param int $a_cat_id category id
	 * @return
	 */
	public function isVisible($a_cat_id)
	{
		return in_array($a_cat_id,$this->categories) or 
			in_array($a_cat_id,(array) $this->subitem_categories);
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
		$this->readConsultationHoursCalendar();
		$this->readBookingCalendar();
		
		include_once('./Services/Membership/classes/class.ilParticipants.php');
		$this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id,'crs'));
		$this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id,'grp'));
		
		$this->addSubitemCalendars();
	}
	
	/**
	 * Read info about selected calendar
	 * @param type $a_cal_id
	 */
	protected function readSelectedCalendar($a_cal_id)
	{
		$this->categories[] = $a_cal_id;
		
		include_once './Services/Calendar/classes/class.ilCalendarCategory.php';
		$cat = new ilCalendarCategory($a_cal_id);
		if($cat->getType() == ilCalendarCategory::TYPE_OBJ)
		{
			$this->readSelectedCategories(array($cat->getObjId()));
			$this->addSubitemCalendars();
		}
	}
	
	/**
	 * Read categories of selected items
	 * 
	 * @param
	 * @return
	 */
	 protected function readSelectedItemCalendars()
	 {
	 	global $ilUser,$ilAccess;
	 	
	 	$this->readPublicCalendars();
	 	$this->readPrivateCalendars();
		$this->readConsultationHoursCalendar();
		$this->readBookingCalendar();

		$obj_ids = array();
		
		$courses = array();
		$groups = array();
		$sessions = array();
		$exercises = array();
		foreach(ilObjUser::_lookupDesktopItems($ilUser->getId(),array('crs','grp','sess','exc')) as $item)
		{
			if($ilAccess->checkAccess('read','',$item['ref_id']))
			{
				switch($item['type'])
				{
					case 'crs':
						$courses[] = $item['obj_id'];
						break;
						
					case 'sess':
						$sessions[] = $item['obj_id'];
						break;

					case 'grp':
						$groups[] = $item['obj_id'];
						break;
					
					case 'exc':
						$exercises[] = $item['obj_id'];
						break;						
				}
			}
		}
		$this->readSelectedCategories($courses); 	
		$this->readSelectedCategories($sessions);	 	
		$this->readSelectedCategories($groups);
		$this->readSelectedCategories($exercises);	 	

		$this->addSubitemCalendars();
		
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
		global $ilDB;
		
		$this->readPublicCalendars();
		$this->readPrivateCalendars();
		$this->readConsultationHoursCalendar();

		#$query = "SELECT ref_id,obd.obj_id obj_id FROM tree t1 ".
		#	"JOIN object_reference obr ON t1.child = obr.ref_id ".
		#	"JOIN object_data obd ON obd.obj_id = obr.obj_id ".
		#	"WHERE t1.lft >= (SELECT lft FROM tree WHERE child = ".$this->db->quote($this->root_ref_id,'integer')." ) ".
		#	"AND t1.lft <= (SELECT rgt FROM tree WHERE child = ".$this->db->quote($this->root_ref_id,'integer')." ) ".
		#	"AND ".$ilDB->in('type',array('crs','grp','sess'),false,'text')." ".
		#	"AND tree = 1";
		
		$subtree_query = $GLOBALS['tree']->getSubTreeQuery(
				$this->root_ref_id,
				array('object_reference.ref_id','object_data.obj_id'),
				array('crs','grp','sess','exc')
		);
		
		$res = $ilDB->query($subtree_query);
		$obj_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($tree->isDeleted($row->ref_id))
			{
				continue;
			}
			
			$obj_type = ilObject::_lookupType($row->obj_id);
			if($obj_type == 'crs' or $obj_type == 'grp')
			{
				// Check for global/local activation
				if(!ilCalendarSettings::_getInstance()->lookupCalendarActivated($row->obj_id))
				{
					continue;
				}
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
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_GLOBAL ,'integer')." ".
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
			$this->categories_info[$row->cat_id]['accepted'] = false;
			$this->categories_info[$row->cat_id]['remote'] = ($row->loc_type == ilCalendarCategory::LTYPE_REMOTE);
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
		global $ilDB;

		// First read private calendars of user
		$query = "SELECT cat_id FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_USR ,'integer')." ".
			"AND obj_id = ".$this->db->quote($ilUser->getId(),'integer')." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cat_ids[] = $row->cat_id;
		}
		
		// Read shared calendars
		include_once('./Services/Calendar/classes/class.ilCalendarSharedStatus.php');
		$accepted_ids = ilCalendarSharedStatus::getAcceptedCalendars($ilUser->getId());
		if(!$cat_ids = array_merge((array) $cat_ids, $accepted_ids))
		{
			return true;
		}
		
		
		// user categories
		$query = "SELECT * FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_USR ,'integer')." ".
			"AND ".$ilDB->in('cat_id',$cat_ids,false,'integer')." ".
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
			
			include_once './Services/Calendar/classes/class.ilCalendarShared.php';
			if(in_array($row->cat_id, $accepted_ids))
			{
				$shared = new ilCalendarShared($row->cat_id);
				if($shared->isEditableForUser($ilUser->getId()))
				{
					$this->categories_info[$row->cat_id]['editable'] = true;
				}
				else
				{
					$this->categories_info[$row->cat_id]['editable'] = false;
				}
			}
			else
			{
				$this->categories_info[$row->cat_id]['editable'] = true;
			}
			
			$this->categories_info[$row->cat_id]['accepted'] = in_array($row->cat_id, $accepted_ids);
			$this->categories_info[$row->cat_id]['remote'] = ($row->loc_type == ilCalendarCategory::LTYPE_REMOTE);
		}
	}
	
	/**
	 * Read personal consultation hours calendar
	 * @param	int	$user_id
	 * @return 
	 */
	public function readConsultationHoursCalendar($a_target_ref_id = NULL)
	{
		global $ilDB;
		
		if(!$this->getCHUserId())
		{
			$this->setCHUserId($this->user_id);
		}
		
		if($a_target_ref_id)
		{
			$target_obj_id = ilObject::_lookupObjId($a_target_ref_id);
			
			$query = 'SELECT DISTINCT(cc.cat_id) FROM booking_entry be '.
					'LEFT JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id '.
					'JOIN cal_entries ce ON be.booking_id = ce.context_id '.
					'JOIN cal_cat_assignments ca ON ce.cal_id = ca.cal_id '.
					'JOIN cal_categories cc ON ca.cat_id = cc.cat_id '.
					'WHERE ((bo.target_obj_id IS NULL) OR bo.target_obj_id = '.$ilDB->quote($target_obj_id,'integer').' ) '.
					'AND cc.obj_id = '.$ilDB->quote($this->getCHUserId(),'integer');
			

			$res = $ilDB->query($query);
			$categories = array();
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$categories[] = $row->cat_id;
			}

			if($categories)
			{
				$query = 'SELECT * FROM cal_categories '.
						'WHERE '.$ilDB->in('cat_id',$categories,false,'integer');
				$res = $ilDB->query($query);
				while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$this->categories[] = $row->cat_id;
					$this->categories_info[$row->cat_id]['obj_id'] = $row->obj_id;
					$this->categories_info[$row->cat_id]['cat_id'] = $row->cat_id;
					$this->categories_info[$row->cat_id]['title'] = $row->title;
					$this->categories_info[$row->cat_id]['color'] = $row->color;
					$this->categories_info[$row->cat_id]['type'] = $row->type;
					$this->categories_info[$row->cat_id]['editable'] = false;
					$this->categories_info[$row->cat_id]['accepted'] = false;
					$this->categories_info[$row->cat_id]['remote'] = false;
				}
			}
		}
		else // no category given
		{
			$query = "SELECT *  FROM cal_categories cc ".
			"WHERE type = ".$ilDB->quote(ilCalendarCategory::TYPE_CH,'integer').' '.
			"AND obj_id = ".$ilDB->quote($this->getCHUserId(),'integer');
			$res = $ilDB->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->categories[] = $row->cat_id;
				$this->categories_info[$row->cat_id]['obj_id'] = $row->obj_id;
				$this->categories_info[$row->cat_id]['cat_id'] = $row->cat_id;
				$this->categories_info[$row->cat_id]['title'] = $row->title;
				$this->categories_info[$row->cat_id]['color'] = $row->color;
				$this->categories_info[$row->cat_id]['type'] = $row->type;
				$this->categories_info[$row->cat_id]['editable'] = false;
				$this->categories_info[$row->cat_id]['accepted'] = false;
				$this->categories_info[$row->cat_id]['remote'] = false;
			}
		}
		return true;
	}

	/**
	 * Read booking manager calendar
	 * @param	int	$user_id
	 * @return
	 */
	public function readBookingCalendar($user_id = NULL)
	{
		global $ilDB;

		if(!$user_id)
		{
			$user_id = $this->user_id;
		}

		$query = "SELECT *  FROM cal_categories ".
			"WHERE type = ".$ilDB->quote(ilCalendarCategory::TYPE_BOOK,'integer').' '.
			"AND obj_id = ".$ilDB->quote($user_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->categories[] = $row->cat_id;
			$this->categories_info[$row->cat_id]['obj_id'] = $row->obj_id;
			$this->categories_info[$row->cat_id]['cat_id'] = $row->cat_id;
			$this->categories_info[$row->cat_id]['title'] = $row->title;
			$this->categories_info[$row->cat_id]['color'] = $row->color;
			$this->categories_info[$row->cat_id]['type'] = $row->type;
			$this->categories_info[$row->cat_id]['editable'] = false;
			$this->categories_info[$row->cat_id]['accepted'] = false;
			$this->categories_info[$row->cat_id]['remote'] = false;
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
		global $ilDB;
		
		if(!count($a_obj_ids))
		{
			return true;
		}

		$query = "SELECT * FROM cal_categories ".
			"WHERE type = ".$this->db->quote(ilCalendarCategory::TYPE_OBJ ,'integer')." ".
			"AND ".$ilDB->in('obj_id',$a_obj_ids,false,'integer')." ".
			"ORDER BY title ";
		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// check activation/deactivation
			$obj_type = ilObject::_lookupType($row->obj_id);
			if($obj_type == 'crs' or $obj_type == 'grp')
			{
				if(!ilCalendarSettings::_getInstance()->lookupCalendarActivated($row->obj_id))
				{
					continue;
				}
			}
		
			$editable = false;
			$exists = false;
			foreach(ilObject::_getAllReferences($row->obj_id) as $ref_id)
			{
				if($ilAccess->checkAccess('edit_event','',$ref_id))
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
			#$this->categories_info[$row->cat_id]['title'] = ilObject::_lookupTitle($row->obj_id);
			$this->categories_info[$row->cat_id]['title'] = $row->title;
			$this->categories_info[$row->cat_id]['obj_type'] = ilObject::_lookupType($row->obj_id);
			$this->categories_info[$row->cat_id]['type'] = $row->type;
			$this->categories_info[$row->cat_id]['remote'] = false;
			
		}
	}
	
	/**
	 * Add subitem calendars
	 * E.g. session calendars in courses, groups
	 * @param
	 * @return
	 */
	protected function addSubitemCalendars()
	{
		global $ilDB;
		
		$course_ids = array();
		foreach($this->categories as $cat_id)
		{
			if($this->categories_info[$cat_id]['obj_type'] == 'crs' or $this->categories_info[$cat_id]['obj_type'] == 'grp')
			{
				$course_ids[] = $this->categories_info[$cat_id]['obj_id'];
			}
		}
		
		$query = "SELECT od2.obj_id sess_id, od1.obj_id crs_id,cat_id FROM object_data od1 ".
			"JOIN object_reference or1 ON od1.obj_id = or1.obj_id ".
			"JOIN tree t ON or1.ref_id = t.parent ".
			"JOIN object_reference or2 ON t.child = or2.ref_id ".
			"JOIN object_data od2 ON or2.obj_id = od2.obj_id ".
			"JOIN cal_categories cc ON od2.obj_id = cc.obj_id ".
			"WHERE ".$ilDB->in('od2.type',array('sess','exc'),false,'text').
			"AND (od1.type = 'crs' OR od1.type = 'grp') ".
			"AND ".$ilDB->in('od1.obj_id',$course_ids,false,'integer').' '.
			"AND or2.deleted IS NULL";
		
		$res = $ilDB->query($query);
		$cat_ids = array();
		$course_sessions = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cat_ids[] = $row->cat_id;
			$course_sessions[$row->crs_id][$row->sess_id] = $row->cat_id;
			$this->subitem_categories[] = $row->cat_id;
		}
		
		foreach($this->categories as $cat_id)
		{
			if(
				($this->categories_info[$cat_id]['obj_type'] == 'crs' ||
				$this->categories_info[$cat_id]['obj_type'] == 'grp' ) &&
				isset($this->categories_info[$cat_id]['obj_id']) &&
				isset($course_sessions[$this->categories_info[$cat_id]['obj_id']]) &&
				is_array($course_sessions[$this->categories_info[$cat_id]['obj_id']]))
			{
				foreach($course_sessions[$this->categories_info[$cat_id]['obj_id']] as $sess_id => $sess_cat_id)
				{
					$this->categories_info[$cat_id]['subitem_ids'][$sess_id] = $sess_cat_id;
					$this->categories_info[$cat_id]['subitem_obj_ids'][$sess_cat_id] = $sess_id;
				}
			}
			else
			{
				$this->categories_info[$cat_id]['subitem_ids'] = array();
				$this->categories_info[$cat_id]['subitem_obj_ids'] = array();
			}
		}
	}
	
	/**
	 * Lookup private categories of user
	 *
	 * @param
	 * @return
	 */
	static function lookupPrivateCategories($a_user_id)
	{
		global $ilDB;
		
		// First read private calendars of user
		$set = $ilDB->query("SELECT * FROM cal_categories ".
			"WHERE type = ".$ilDB->quote(ilCalendarCategory::TYPE_USR ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_user_id,'integer'));
		$cats = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$cats[] = $rec;
		}
		return $cats;
	}
	
}
?>