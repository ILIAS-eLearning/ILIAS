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
	/**
	 * get all categories of an user
	 *
	 * @access public
	 * @param int user_id
	 * @return array array(ilCalendarCategory)
	 * @static
	 */
	public static function _getCategoriesOfUser($a_user_id)
	{
		global $ilDB;
		
		$query = "SELECT cat_id FROM cal_categories ".
			"WHERE obj_id = ".$ilDB->quote($a_user_id)." ".
			"AND type = ".$ilDB->quote(ilCalendarCategory::TYPE_USR)." ".
			"ORDER BY title ";
		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$categories[] = new ilCalendarCategory($row->cat_id);
		}
		return $categories ? $categories : array();
	}
	
	/**
	 * get available (hidden and visible) categories of user
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function _getAvailableCategoriesOfUser($a_user_id)
	{
		global $ilDB;
		
		$query = "SELECT cat_id FROM cal_categories ".
			"WHERE obj_id = ".$ilDB->quote($a_user_id)." ".
			"AND type = ".$ilDB->quote(ilCalendarCategory::TYPE_USR)." ";
		$res = $ilDB->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$categories[] = $row->cat_id;
		}
		return $categories ? $categories : array();
	}
	
	/**
	 * get all object categories
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function _getObjectCategories()
	{
		global $ilDB;
		
		$query = "SELECT cat_id FROM cal_categories ".
			"WHERE type = ".$ilDB->quote(ilCalendarCategory::TYPE_OBJ)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$categories[] = new ilCalendarCategory($row->cat_id);
		}
		return $categories ? $categories : array();
		
	}
	
	/**
	 * prepare categories of users for selection
	 *
	 * @access public
	 * @param int user id
	 * @return
	 * @static
	 */
	public static function _prepareCategoriesOfUserForSelection($a_user_id)
	{
		foreach(ilCalendarCategories::_getCategoriesOfUser($a_user_id) as $cat)
		{
			$cats[$cat->getCategoryID()] = $cat->getTitle();
		}
		return $cats ? $cats : array();
	}
	
}
?>