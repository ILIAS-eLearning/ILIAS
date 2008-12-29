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

include_once './Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRule.php';

/** 
* Shibboleth role assignment rules
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup AuthShibboleth
*/
class ilShibbolethRoleAssignmentRules
{
	protected static $active_plugins = null;
	
	public static function getAllRules()
	{
		global $ilDB;
		
		$query = "SELECT rule_id FROM shib_role_assignment ORDER BY rule_id";
		$res  =$ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rules[$row->rule_id] = new ilShibbolethRoleAssignmentRule($row->rule_id);
		}
		return $rules ? $rules : array();
	}
	
	public static function getCountRules()
	{
		global $ilDB;
		
		$query = "SELECT COUNT(*) num FROM shib_role_assignment ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->num;
		}
		return 0;
	}
	
	public static function updateAssignments($a_usr_id,$a_data)
	{
		global $ilDB,$rbacadmin,$rbacreview,$ilSetting,$ilLog;
		
		$query = "SELECT rule_id,add_on_update,remove_on_update FROM shib_role_assignment ".
			"WHERE add_on_update = 1 OR remove_on_update = 1";
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rule = new ilShibbolethRoleAssignmentRule($row->rule_id);
			
			$matches = $rule->matches($a_data);
			if($matches and $row->add_on_update)
			{
				$ilLog->write(__METHOD__.': Assigned to role '.ilObject::_lookupTitle($rule->getRoleId()));
				$rbacadmin->assignUser($rule->getRoleId(),$a_usr_id);
			}
			if(!$matches and $row->remove_on_update)
			{
				$ilLog->write(__METHOD__.': Deassigned from role '.ilObject::_lookupTitle($rule->getRoleId()));
				$rbacadmin->deassignUser($rule->getRoleId(),$a_usr_id);
			}
		}
		
		// check if is assigned to minimum one global role
		if(!array_intersect($rbacreview->assignedRoles($a_usr_id),$rbacreview->getGlobalRoles()))
		{
			$ilLog->write(__METHOD__.': Assigned to default role '.ilObject::_lookupTitle($ilSetting->get('shib_user_default_role')));
			$rbacadmin->assignUser($ilSetting->get('shib_user_default_role'),$a_usr_id);
		}
		
		return true;
	}
	
	public static function doAssignments($a_usr_id,$a_data)
	{
		global $ilDB,$ilSetting,$rbacadmin,$ilLog;
		
		$query = "SELECT rule_id FROM shib_role_assignment ";
		
		$num_matches = 0;
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$rule = new ilShibbolethRoleAssignmentRule($row->rule_id);
			if($rule->matches($a_data))
			{
				$num_matches++;
				$ilLog->write(__METHOD__.': Assigned to role '.ilObject::_lookupTitle($rule->getRoleId()));
				$rbacadmin->assignUser($rule->getRoleId(),$a_usr_id);
			}
		}
		// Assign to default if no matching found
		if(!$num_matches)
		{
			$ilLog->write(__METHOD__.': Assigned to default role '.ilObject::_lookupTitle($ilSetting->get('shib_user_default_role')));
			$rbacadmin->assignUser($ilSetting->get('shib_user_default_role'),$a_usr_id);
		}
		return true;
	}
	
	public static function callPlugin($a_plugin_id,$a_user_data)
	{
		global $ilPluginAdmin;
		
		if(self::$active_plugins == null)
		{
			self::$active_plugins = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE,
				'AuthShibboleth',
				'shibhk');
		}
		
		$assigned = false;
		foreach(self::$active_plugins as $plugin_name)
		{
			$ok = false;
			$plugin_obj = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE,
				'AuthShibboleth',
				'shibhk',
				$plugin_name);
			
			if($plugin_obj instanceof ilShibbolethRoleAssignmentPlugin)
			{
				$ok = $plugin_obj->checkRoleAssignment($a_plugin_id,$a_user_data);
			}
			
			if($ok)
			{
				$assigned = true;
			}
		}
		return $assigned;
	}
}
?>
