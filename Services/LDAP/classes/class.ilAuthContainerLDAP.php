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

include_once('Auth/Container/LDAP.php');

/** 
* Overwritten Pear class AuthContainerLDAP
* This class is overwritten to support nested groups
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup 
*/
class ilAuthContainerLDAP extends Auth_Container_LDAP
{
	private $log = null;
	private $server = null;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array array of pear parameters
	 * 
	 */
	public function __construct(ilLDAPServer $server,$a_params)
	{
		global $ilLog;
		
		$this->server = $server;
	 	parent::__construct($a_params);
	 	$this->log = $ilLog;
	}
	
	/**
	 * check group overwritten base class
	 *
	 * @access public
	 * @param string user name (DN or external account name)
	 * 
	 */
	
	public function checkGroup($a_name)
	{
		$this->log->write('LDAP: checking group restrictions...');

		// if there are multiple groups define check all of them for membership
		$groups = $this->server->getGroupNames();
		if(count($groups) <= 1)
		{
			return parent::checkGroup($a_name);
		}
		
		foreach($groups as $group)
		{
			$this->options['group'] = $group;
			
			if(parent::checkGroup($a_name))
			{
				return true;
			}
		}
	 	return false;	
	}
	/**
	 * Overwritten debug method
	 * Writes infos to log file
	 *
	 * @access private
	 * @param string message
	 * @param int line
	 * 
	 */
	public function _debug($a_message,$a_line = 0)
	{
	 	$this->log->write('LDAP PEAR: '.$a_message);
	 	parent::_debug($a_message,$a_line);
	}
	
}



?>