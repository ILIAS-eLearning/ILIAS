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
* RADIUS authentication
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @package ilias
*/

include_once 'Auth/Auth.php';
include_once './classes/class.ilBaseAuthentication.php';

class ilRADIUSAuthentication extends ilBaseAuthentication
{
	function ilRADIUSAuthentication()
	{
		parent::ilBaseAuthentication();
		$this->__setMessageCode('Client');
	}
	
	function _validateServers($a_servers)
	{
		global $ilias;
		
		$servers = explode(",",$a_servers);
		
		foreach ($servers as $server)
		{
			$server = trim($server);

			if (!ilUtil::isIPv4($server) and !ilUtil::isDN($server))
			{
				return false;
			}
		}
		
		return true;
	}
	
	function _getServers($a_db_handler = '')
	{
		global $ilDB;
		
		$db =& $ilDB;
		
		if ($a_db_handler != '')
		{
			$db =& $a_db_handler;
		}
		
		$q = "SELECT value FROM settings WHERE keyword LIKE 'radius_server%' ORDER BY keyword ASC";
		$r = $db->query($q);
		
		$servers = array();
		
		while ($row = $r->fetchRow())
		{
			$servers[] = $row[0];
		}
		
		return $servers;
	}
	
	function _saveServers($a_servers)
	{
		global $ilias;
		
		$old_servers = ilRADIUSAuthentication::_getServers();
		$count = count($old_servers);
		
		$servers = explode(",",$a_servers);
		
		$new_count = count($servers);
		
		$i = 1;
		
		foreach ($servers as $server)
		{
			if ($i == 1)
			{
				$ilias->setSetting('radius_server',$server);
			}
			else
			{
				$ilias->setSetting('radius_server'.$i,$server);
			}
			
			$i++;
		}
		
		// delete surplus old servers
		for ($n = $new_count + 1; $n <= $count; $n++)
		{
			$ilias->deleteSetting('radius_server'.$n);
		}
	}
}
?>
