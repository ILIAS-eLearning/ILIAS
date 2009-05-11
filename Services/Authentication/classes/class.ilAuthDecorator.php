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

/** 
* Base class for ilAuth, ilAuthHTTP ....
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesAuthentication
*/
abstract class ilAuthDecorator
{
	private $auth = null;
	private $container = null;
	
	protected $options = array();
	
	/**
	 * Constructor
	 */
	public function __construct(ilAuthContainerDecorator $container)
	{
		$this->setContainer($container);
	}

	/**
	 * 
	 */
	abstract public function initAuth();
	
	/**
	 * Wrapper for all PEAR_Auth methods 
	 * @return mixed
	 */
	public final function __call($name,$arguments)
	{
		return call_user_func_array(array($this->auth,$name),$arguments);
	}
	
	public function setAuthObject($a_auth)
	{
		$this->auth = $a_auth;
		$this->getContainer()->getContainer()->_auth_obj = $this->auth;
	}
	
	public function getAuthObject()
	{
		return $this->auth;
	}
	
	/**
	 * set auth container
	 */
	public function setContainer(ilAuthContainerDecorator $a_container)
	{
		$this->container = $a_container;
	}
	
	/**
	 * get instance of ilAuthContainerDecorator
	 */
	public function getContainer()
	{
		return $this->container;
	}
	
	/**
	 * Append global Auth_Option
	 */
	public function appendOption($a_key,$a_value)
	{
		$this->options[$a_key] = $a_value;
	}
	
	/**
	 * get global AUTH option
	 */
	public function getOptions()
	{
		return $this->options ? $this->options : array();
	}
	
	/**
	 * init callback functions
	 * @return 
	 */
	protected function initCallbacks()
	{
		$this->setLoginCallback(array($this->getContainer(),'loginObserver'));
		$this->setFailedLoginCallback(array($this->getContainer(),'failedLoginObserver'));
		$this->setCheckAuthCallback(array($this->getContainer(),'checkAuthObserver'));
		$this->setLogoutCallback(array($this->getContainer(),'logoutObserver'));
		
		if(method_exists($this->getAuthObject(),'attachLogObserver'))
		{
			if(@include_once('Log.php'))
			{
				if(@include_once('Log/observer.php'))
				{
					include_once('Services/Authentication/classes/class.ilAuthLogObserver.php');
					$this->getAuthObject()->attachLogObserver(
						new ilAuthLogObserver(AUTH_LOG_DEBUG));
					$this->getAuthObject()->enableLogging = true;
				}
		 	}
 		}
	}
}
?>
