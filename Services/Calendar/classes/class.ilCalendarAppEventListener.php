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

include_once('./Services/EventHandling/interfaces/interface.ilAppEventListener.php');

/**
* Handles events (create,update,delete) for autmatic generated calendar 
* events from course, groups, ...  
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/
class ilCalendarAppEventListener implements ilAppEventListener
{
	/**
	 * Handle events like create, update, delete
	 *
	 * @access public
	 * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	 * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)	 * 
	 * @static
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		global $ilLog,$ilUser;
		
		$delete_cache = false;
		
		switch($a_component)
		{
			case 'Modules/Session':
			case 'Modules/Group':
			case 'Modules/Course':
				switch($a_event)
				{
					case 'create':
						$ilLog->write(__METHOD__.': Handling create event');
						self::createCategory($a_parameter['object']);
						self::createAppointments($a_parameter['object'],$a_parameter['appointments']);
						$delete_cache = true;
						break;
					
					case 'update':
						$ilLog->write(__METHOD__.': Handling update event');
						self::updateCategory($a_parameter['object']);
						self::deleteAppointments($a_parameter['obj_id']);
						self::createAppointments($a_parameter['object'],$a_parameter['appointments']);
						$delete_cache = true;
						break;
					
					case 'delete':
						$ilLog->write(__METHOD__.': Handling delete event');
						self::deleteCategory($a_parameter['obj_id']);
						$delete_cache = true;
						break;
				}
				break;
				
			case 'Services/Booking':
				switch($a_event)
				{
					case 'create':
						break;
					case 'update':
						break;
					case 'delete':
						break;
				}
				break;
				
			case 'Modules/Exercise':				
				switch($a_event)
				{
					case 'createAssignment':
						$ilLog->write(__METHOD__.': Handling create event (exercise assignment)');
						self::createCategory($a_parameter['object'],true); // exercise category could already exist
						self::createAppointments($a_parameter['object'],$a_parameter['appointments']);
						$delete_cache = true;
						break;
					
					case 'updateAssignment':
						$ilLog->write(__METHOD__.': Handling update event (exercise assignment)');
						self::createCategory($a_parameter['object'],true); // different life-cycle than ilObject
						self::deleteAppointments($a_parameter['obj_id'],$a_parameter['context_ids']);
						self::createAppointments($a_parameter['object'],$a_parameter['appointments']);
						$delete_cache = true;
						break;
					
					case 'deleteAssignment':
						$ilLog->write(__METHOD__.': Handling delete event (exercise assignment)');
						self::deleteAppointments($a_parameter['obj_id'],$a_parameter['context_ids']);
						$delete_cache = true;
						break;
					
					case 'delete':
						$ilLog->write(__METHOD__.': Handling delete event');
						self::deleteCategory($a_parameter['obj_id']);
						$delete_cache = true;
						break;
				}				
				break;
		}
		
		if($delete_cache)
		{
			include_once './Services/Calendar/classes/class.ilCalendarCategories.php';
			ilCalendarCategories::deletePDItemsCache($ilUser->getId());
			ilCalendarCategories::deleteRepositoryCache($ilUser->getId());
		}
	}
	
	/**
	 * Create a category for a new object (crs,grp, ...)
	 * 
	 * @access public
	 * @param object ilias object ('crs','grp',...)
	 * @static
	 */
	public static function createCategory($a_obj, $a_check_existing = false)
	{
		global $lng;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentColors.php');
		
		// already existing?  do update instead
		if($a_check_existing &&
			ilCalendarCategory::_getInstanceByObjId($a_obj->getId()))
		{
			return self::updateCategory($a_obj);
		}
		
		$cat = new ilCalendarCategory();
		$cat->setTitle($a_obj->getTitle() ? $a_obj->getTitle() : $lng->txt('obj_'.$a_obj->getType()));
		$cat->setType(ilCalendarCategory::TYPE_OBJ);
		$cat->setColor(ilCalendarAppointmentColors::_getRandomColorByType($a_obj->getType()));
		$cat->setObjId($a_obj->getId());
		return $cat->add();
	}
	
	/**
	 * Create a category for a new object (crs,grp, ...)
	 * 
	 * @access public
	 * @param object ilias object ('crs','grp',...)
	 * @static
	 */
	public static function updateCategory($a_obj)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategory.php');
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentColors.php');
		
		if($cat = ilCalendarCategory::_getInstanceByObjId($a_obj->getId()))
		{
			$cat->setTitle($a_obj->getTitle());
			$cat->update();
		}
		return true;
	}

	/**
	 * Create appointments
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function createAppointments($a_obj,$a_appointments)
	{
		global $ilLog;

		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		
		if(!$cat_id = ilCalendarCategories::_lookupCategoryIdByObjId($a_obj->getId()))
		{
			$ilLog->write(__METHOD__.': Cannot find calendar category for obj_id '.$a_obj->getId());
			$cat_id = self::createCategory($a_obj);
		}
		
		foreach($a_appointments as $app_templ)
		{
			$app = new ilCalendarEntry();
			$app->setContextId($app_templ->getContextId());
			$app->setTitle($app_templ->getTitle());
			$app->setSubtitle($app_templ->getSubtitle());
			$app->setDescription($app_templ->getDescription());
			$app->setFurtherInformations($app_templ->getInformation());
			$app->setLocation($app_templ->getLocation());
			$app->setStart($app_templ->getStart());
			$app->setEnd($app_templ->getEnd());
			$app->setFullday($app_templ->isFullday());
			$app->setAutoGenerated(true);
			$app->setTranslationType($app_templ->getTranslationType());
			$app->save();
			
			$ass = new ilCalendarCategoryAssignments($app->getEntryId());
			$ass->addAssignment($cat_id);
		}
	}
	
	/**
	 * Delete automatic generated appointments
	 * 
	 * @access public
	 * @param int obj_id
	 * @param array context ids
	 * @static
	 */
	public static function deleteAppointments($a_obj_id, array $a_context_ids = null)
	{
		include_once('./Services/Calendar/classes/class.ilCalendarCategoryAssignments.php');
		include_once('./Services/Calendar/classes/class.ilCalendarEntry.php');
		
		foreach(ilCalendarCategoryAssignments::_getAutoGeneratedAppointmentsByObjId($a_obj_id) as $app_id)
		{
			// delete only selected entries
			if(is_array($a_context_ids))
			{
				$entry = new ilCalendarEntry($app_id);
				if(!in_array($entry->getContextId(), $a_context_ids))
				{
					continue;
				}
			}
			
			ilCalendarCategoryAssignments::_deleteByAppointmentId($app_id);
			ilCalendarEntry::_delete($app_id);
		}
	}
	
	/**
	 * delete category
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function deleteCategory($a_obj_id)
	{
		global $ilLog;
		
		include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
		
		if(!$cat_id = ilCalendarCategories::_lookupCategoryIdByObjId($a_obj_id))
		{
			$ilLog->write(__METHOD__.': Cannot find calendar category for obj_id '.$a_obj_id);
			return false;
		}
		
		$category = new ilCalendarCategory($cat_id);
		$category->delete();
		
	}
}
?>