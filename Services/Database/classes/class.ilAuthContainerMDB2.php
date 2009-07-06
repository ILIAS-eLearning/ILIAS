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

include_once 'Auth/Container/MDB2.php';

/** 
* Authentication against ILIAS database
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesDatabase
*/
class ilAuthContainerMDB2 extends Auth_Container_MDB2
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $ilClientIniFile;
		
		$options['dsn']			= IL_DSN;
		$options['table']		= $ilClientIniFile->readVariable('auth', 'table');
		$options['usernamecol']	= $ilClientIniFile->readVariable('auth', 'usercol');
		$options['passwordcol']	= $ilClientIniFile->readVariable('auth', 'passcol');
		
		parent::__construct($options);
	}
	
	
	/**
	 * Static function removes Microsoft domain name from username
	 */
	public static function toUsernameWithoutDomain($username)
	{
		// Remove all characters including the last slash or the last backslash
		// in the username
		$pos = strrpos($username, '/');
		$pos2 = strrpos($username, '\\');
		if ($pos === false || $pos < $pos2) 
		{
			$pos = $pos2;
		}
		if ($pos !== false)
		{
			$username = substr($username, $pos + 1);
		}
		return $username;
	}
	
}
?>