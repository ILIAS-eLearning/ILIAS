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
 * @classDescription Base class for all PEAR and ILIAS auth classes.
 * Enables logging, observers.
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ingroup ServicesAuthentication
 */
abstract class ilAuthBase
{
	// Used for SOAP Auth
	// TODO: Find another solution
	protected $sub_status = null;
	
	
	/**
	 * Get container object
	 * @return	object ilAuthContainerBase
	 */
	public final function getContainer()
	{
		return $this->storage;
	}
	
	/**
	 * Init auth object
	 * Enable logging, set callbacks...
	 * @return void
	 */
	protected final function initAuth()
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Init callbacks');
		$this->setLoginCallback(array($this,'loginObserver'));
		$this->setFailedLoginCallback(array($this,'failedLoginObserver'));
		$this->setCheckAuthCallback(array($this,'checkAuthObserver'));
		$this->setLogoutCallback(array($this,'logoutObserver'));
		
		// TODO: add log observer
		include_once('Services/Authentication/classes/class.ilAuthLogObserver.php');
		$this->attachLogObserver(new ilAuthLogObserver(AUTH_LOG_DEBUG));
		$this->enableLogging = true;
		
	}
	
	/**
	 * Called after successful login
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function loginObserver($a_username,$a_auth)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Login observer called');
		$this->getContainer()->loginObserver($a_username,$a_auth);
	}
	
	
	/**
	 * Called after failed login
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function failedLoginObserver($a_username,$a_auth)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Failed login observer called');
		$this->getContainer()->failedLoginObserver($a_username,$a_auth);
	}

	/**
	 * Called after each check auth request
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function checkAuthObserver($a_username,$a_auth)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Check auth observer called');
		return $this->getContainer()->checkAuthObserver($a_username,$a_auth);
	}
	
	/**
	 * Called after logout
	 * @return 
	 * @param array $a_username
	 * @param object $a_auth
	 */
	protected function logoutObserver($a_username,$a_auth)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': Logout observer called');
		$this->getContainer()->logoutObserver($a_username,$a_auth);
	}
	
}
