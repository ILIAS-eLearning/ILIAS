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

include_once 'Auth/Container/Multiple.php';

include_once './Services/Authentication/classes/class.ilAuthUtils.php';
include_once './Services/Authentication/classes/class.ilAuthContainerDecorator.php';
include_once './Services/Authentication/classes/class.ilAuthModeDetermination.php';


/**   
* @author Stefan Meyer <smeyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAuthentication
*/
class ilAuthContainerMultiple extends ilAuthContainerDecorator
{
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->initContainer();
	}
	
	protected function initMultipleParams()
	{
		include_once 'Auth/Container/Multiple.php';

		$multiple_params = array();
		
		// Determine sequence of authentication methods
		foreach(ilAuthModeDetermination::_getInstance()->getAuthModeSequence() as $auth_mode)
		{
			if($auth_mode == AUTH_LDAP)
			{
				include_once './Services/LDAP/classes/class.ilAuthContainerLDAP.php';
				
				$multiple_params[] = array(
					'type'		=> 'LDAP',
					'container' => $tmp = new ilAuthContainerLDAP(),
					'options'	=> $tmp->getParameters()
				);
			}			
			if($auth_mode == AUTH_LOCAL)
			{
				include_once './Services/Database/classes/class.ilAuthContainerMDB2.php';
				
				$multiple_params[] = array(
					'type'		=>	'MDB2',
					'container'	=>	$tmp = new ilAuthContainerMDB2(),
					'options'	=> 	$tmp->getParameters()
				);
			}
		}
		return $multiple_params ? $multiple_params : array();
	}
	
	/**
	 * Init PEAR container
	 * @return bool 
	 */
	protected function initContainer()
	{
		$this->setContainer(
			new Auth_Container_Multiple($this->initMultipleParams()));
		return true;
	}
}
?>