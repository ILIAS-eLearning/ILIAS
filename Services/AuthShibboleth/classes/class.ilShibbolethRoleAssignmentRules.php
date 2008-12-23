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
}
?>
