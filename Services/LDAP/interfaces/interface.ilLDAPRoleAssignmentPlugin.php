<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Interface for ldap role assignment plugins
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesLDAP
*/
interface ilLDAPRoleAssignmentPlugin
{
	
	/**
	 * check role assignment for a specific plugin id 
	 * (defined in the ldap role assignment administration).
	 * 
	 * @param int	$a_plugin_id	Unique plugin id
	 * @param array $a_user_data	Array with user data ($_SERVER)
	 * @return bool whether the condition is fullfilled or not	
	 */
	public function checkRoleAssignment($a_plugin_id,$a_user_data);
	
	/**
	 * If additional LDAP attributes vales are required in the plugin return an array
	 * with these attribute names.
	 * <code>
	 * public function getAdditionalAttributeNames()
	 * {
	 * 		return array('employeetype','employeenumber','loginshell');
	 * }
	 * </code>
	 * @return 
	 */
	public function getAdditionalAttributeNames();
}
?>
