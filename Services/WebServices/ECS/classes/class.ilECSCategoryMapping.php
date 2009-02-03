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

include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMappingRule.php';

/** 
* 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesWebServicesECS 
*/
class ilECSCategoryMapping
{
	private static $cached_active_rules = null;

	/**
	 * get active rules
	 *
	 * @return array
	 * @static
	 */
	public static function getActiveRules()
	{
		global $ilDB;
		
		$res = $ilDB->query('SELECT mapping_id FROM ecs_container_mapping');
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rules[] = new ilECSCategoryMappingRule($row->mapping_id);
		}
		return $rules ? $rules : array();
	}
	
	/**
	 * get matching category
	 *
	 * @param object	$econtent	ilECSEcontent
	 * @return
	 * @static
	 */
	public static function getMatchingCategory(ilECSEContent $econtent)
	{
		global $ilLog;
		
		if(is_null(self::$cached_active_rules))
		{
			self::$cached_active_rules = self::getActiveRules();
		}
		foreach(self::$cached_active_rules as $rule)
		{
			if($rule->matches($econtent))
			{
				$ilLog->write(__METHOD__.': Found assignment for field type: '.$rule->getFieldName());
				return $rule->getContainerId();
			}
			$ilLog->write(__METHOD__.': Category assignment failed for field: '.$rule->getFieldName());
		}
		// Return default container
		$ilLog->write(__METHOD__.': Using default container');
		return ilECSSettings::_getInstance()->getImportId();
	}
	
	
	
	/**
	 * 
	 *
	 * @return
	 * @static
	 */
	public static function getPossibleFields()
	{
		return array(
			'part_id',
			'study_courses',
			'courseType',
			'term',
			'credits',
			'begin');
	}
}
?>
