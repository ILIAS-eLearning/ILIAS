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

include_once 'Auth/Container.php';

include_once './Services/Authentication/classes/class.ilAuthUtils.php';
include_once './Services/Authentication/classes/class.ilAuthModeDetermination.php';


/**   
* @author Stefan Meyer <smeyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ServicesAuthentication
*/
class ilAuthContainerMultiple extends Auth_Container
{
	protected $current_container = null;

	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct();
		
		include_once './Services/Database/classes/class.ilAuthContainerMDB2.php';
		$this->current_container = new ilAuthContainerMDB2();
	}
	
    /**
     * @see ilAuthContainerBase::failedLoginObserver()
     */
    public function failedLoginObserver($a_username, $a_auth)
    {
        $this->log('Auth_Container_Multiple: All containers rejected user credentials.', AUTH_LOG_DEBUG);
		return false;
    }
    
    /**
     * @see ilAuthContainerBase::loginObserver()
     */
    public function loginObserver($a_username, $a_auth)
    {
		$this->log('Container Multiple: loginObserver'.get_class($this->current_container),AUTH_LOG_DEBUG);
		// Forward to current container
		if($this->current_container instanceof Auth_Container)
		{
			$this->log('Container Multiple: Forwarding to '.get_class($this->current_container),AUTH_LOG_DEBUG);
			return $this->current_container->loginObserver($a_username, $a_auth);
		}
		return false;
    }
	
    /**
     * @see ilAuthContainerBase::checkAuthObserver()
     */
    public function checkAuthObserver($a_username, $a_auth)
    {
		$this->log('Container Multiple: checkAuthObserver',AUTH_LOG_DEBUG);
		// Forward to current container
		if($this->current_container instanceof Auth_Container)
		{
			$this->log('Container Multiple: Forwarding to '.get_class($this->current_container),AUTH_LOG_DEBUG);
			return $this->current_container->checkAuthObserver($a_username, $a_auth);
		}
		return false;
    }

	
	public function fetchData($user,$pass)
	{
		foreach(ilAuthModeDetermination::_getInstance()->getAuthModeSequence() as $auth_mode)
		{
			if ($_REQUEST['force_mode_apache']) 
			{
				$this->log('Container Apache: Trying new container',AUTH_LOG_DEBUG);
				include_once './Services/AuthApache/classes/class.ilAuthContainerApache.php';
				$this->current_container = new ilAuthContainerApache();
				
				$auth = new ilAuthApache($this->current_container);
			}
			else
			{
				switch($auth_mode)
				{
					case AUTH_LDAP:
						$this->log('Container LDAP: Trying new container',AUTH_LOG_DEBUG);
						include_once './Services/LDAP/classes/class.ilAuthContainerLDAP.php';
						$this->current_container = new ilAuthContainerLDAP();
						break;
					
					case AUTH_LOCAL:
						$this->log('Container MDB2: Trying new container',AUTH_LOG_DEBUG);
						include_once './Services/Database/classes/class.ilAuthContainerMDB2.php';
						$this->current_container = new ilAuthContainerMDB2();
						break;
						
					case AUTH_SOAP:
						$this->log('Container SOAP: Trying new container',AUTH_LOG_DEBUG);
						include_once './Services/SOAPAuth/classes/class.ilAuthContainerSOAP.php';
						$this->current_container = new ilAuthContainerSOAP();
						break;
						
					case AUTH_RADIUS:
						$this->log('Container Radius: Trying new container',AUTH_LOG_DEBUG);
						include_once './Services/Radius/classes/class.ilAuthContainerRadius.php';
						$this->current_container = new ilAuthContainerRadius();
						break;
					
					// begin-patch auth_plugin
					default:
						$this->log('Container Plugin: Trying new container',AUTH_LOG_DEBUG);
						foreach(ilAuthUtils::getAuthPlugins() as $pl)
						{
							$container = $pl->getContainer($auth_mode);
							if($container instanceof Auth_Container)
							{
								$this->current_container = $container;
								break;
							}
						}
						break;
					// end-patch auth_plugin
					
				}
			}
            $this->current_container->_auth_obj = $this->_auth_obj;
			
            $result = $this->current_container->fetchData($user, $pass);

            if (PEAR::isError($result)) 
			{
                $this->log('Container '.$key.': '.$result->getMessage(), AUTH_LOG_ERR);
				// Do not return here, otherwise wrong configured auth modes might block ilias database authentication
            } 
			elseif ($result == true) 
			{
                $this->log('Container '.$key.': Authentication successful.', AUTH_LOG_DEBUG);
                return true;
            } 
			else 
			{
                $this->log('Container '.$key.': Authentication failed.', AUTH_LOG_DEBUG);
            }
		}
        return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCaptchaVerification()
	{
		return true;
	}
}
?>