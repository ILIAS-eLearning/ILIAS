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
* HTTPS
*
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package application
*/
class ilHTTPS
{
	var $enabled = false;
	var $protected_scripts = array();

	function ilHTTPS()
	{
		global $ilias;

		if($this->enabled = (bool) $ilias->getSetting('https'))
		{
			$this->__readProtectedScripts();
		}
	}
	
	function checkPort()
	{
		if(!$this->enabled)
		{
			return true;
		}
		if(in_array(basename($_SERVER["SCRIPT_NAME"]),$this->protected_scripts) and
		   $_SERVER["HTTPS"] != 'on')
		{
			header("location: https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
			exit;
		}
		if(!in_array(basename($_SERVER["SCRIPT_NAME"]),$this->protected_scripts) and
		   $_SERVER["HTTPS"] == 'on')
		{
			header("location: http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
			exit;
		}
		return true;
	}

	function __readProtectedScripts()
	{
		$this->protected_scripts[] = 'login.php';
		$this->protected_scripts[] = 'start_bmf.php';

		return true;
	}

	/**
	* static method to check if https connections are possible for this server
	* @access	public
	* @return	boolean
	*/
	function _checkHTTPS()
	{
		// only check standard port in the moment
		$port = 443;

		if(($sp = @fsockopen($_SERVER["SERVER_NAME"],$port,$errno,$error)) === false)
		{
			return false;
		}
		fclose($sp);
		return true;
	}
	/**
	* static method to check if http connections are possible for this server
	* 
	* @access	public
	* @return	boolean
	*/
	function _checkHTTP()
	{
		$port = 80;
		
		if(($sp = @fsockopen($_SERVER["SERVER_NAME"],$port,$errno,$error)) === false)
		{
			return false;
		}
		fclose($sp);
		return true;
	}	
}
?>