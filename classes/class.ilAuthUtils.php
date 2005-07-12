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
* static utility functions used to manage authentication modes
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
* @package ilias
*
*/

class ilAuthUtils
{
	function _getAuthModeOfUser($a_username,$a_password,&$a_db_handler)
	{
		global $ilDB;
		
		$db =& $ilDB;
		
		if ($a_db_handler != '')
		{
			$db =& $a_db_handler;
		}
		
		$q = "SELECT auth_mode FROM usr_data WHERE ".
			 "login='".$_POST['username']."' AND ".
			 "passwd='".md5($_POST['password'])."'";
		$r = $this->db->query($q);
		
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
		
		return ilAuthUtils::_getAuthMode($row->auth_mode,$db);
	}
	
	function _getAuthMode($a_auth_mode,&$a_db_handler)
	{
		global $ilDB;
		
		$db =& $ilDB;
		
		if ($a_db_handler != '')
		{
			$db =& $a_db_handler;
		}

		switch ($a_auth_mode)
		{
			case "local":
				return AUTH_LOCAL;
				break;
				
			case "ldap":
				return AUTH_LDAP;
				break;
				
			case "radius":
				return AUTH_RADIUS;
				break;
				
			case "script":
				return AUTH_SCRIPT;
				break;
				
			case "shibboleth":
				return AUTH_SHIBBOLETH;
				break;
				
			default:
				$q = "SELECT value FROM settings WHERE ".
			 		 "keyword='auth_mode'";
				$r = $db->query($q);
				$row = $r->fetchRow();
				return $row[0];
				break;	
		}
	}
	
	function _getAuthModeName($a_auth_key)
	{
		global $ilias;

		switch ($a_auth_key)
		{
			case AUTH_LOCAL:
				return "local";
				break;
				
			case AUTH_LDAP:
				return "ldap";
				break;
				
			case AUTH_RADIUS:
				return "radius";
				break;
				
			case AUTH_SCRIPT:
				return "script";
				break;
				
			case AUTH_SHIBBOLETH:
				return "shibboleth";
				break;
				
			default:
				return "default";
				break;	
		}
	}
	
	function _getActiveAuthModes()
	{
		global $ilias;
		
		$modes = array(
						'default'	=> $ilias->getSetting("auth_mode"),
						'local'		=> AUTH_LOCAL
						);
		
		if ($ilias->getSetting("ldap_active")) $modes['ldap'] = AUTH_LDAP;
		if ($ilias->getSetting("radius_active")) $modes['radius'] = AUTH_RADIUS;
		if ($ilias->getSetting("shibboleth_active")) $modes['shibboleth'] = AUTH_SHIBBOLETH;
		if ($ilias->getSetting("script_active")) $modes['script'] = AUTH_SCRIPT;

		return $modes;
	}
	
}
?>
