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
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup 
*/

include_once('./Services/Authentication/classes/class.ilAuthUtils.php');

class ilAuthMultiple
{
	protected $settings = null;
	protected $auth = null;
	protected $auth_modes = null;
	protected $current_auth_modes = null;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct()
	{
	 	include_once('./Services/Authentication/classes/class.ilAuthModeDetermination.php');
		$this->settings = ilAuthModeDetermination::_getInstance();
		
		
		$this->auth_modes = $this->settings->getAuthModeSequence();
		$this->initNextAuthObject();
	}
	
	/**
	 * set idle 
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setIdle($time,$add = false)
	{
	 	$this->auth->setIdle($time,$add);
	}
	
	/**
	 * set expire
	 *
	 * @access public
	 */
	public function setExpire($time,$add = false)
	{
	 	$this->auth->setExpire($time,$add);
	}
	
	/**
	 * logout
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function logout()
	{
	 	$this->auth->logout();
	}
	
	/**
	 * check auth
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function checkAuth()
	{
		global $ilLog;
		
		do
	 	{
		 	if($this->auth->checkAuth())
		 	{
	 			return true;
	 		}
	 		$this->auth->logout();
	 		
	 	} 
	 	while($this->initNextAuthObject());
	 	
	 	$ilLog->write(__METHOD__.': Authentication failed.');
	 	return false;
	}
	
	/**
	 * get username
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getUsername()
	{
	 	return $this->auth->getUsername();
	}
	
	/**
	 * get auth
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getAuth()
	{
	 	return $this->checkAuth();
	}
	
	/**
	 * start
	 *
	 * @access public
	 * 
	 */
	public function start()
	{
	 	$this->auth->start();
	}
	
	/**
	 * get status
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getStatus()
	{
	 	return $this->auth->getStatus();
	}
	
	
	/**
	 * init next auth object
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function initNextAuthObject()
	{
	 	global $ilLog,$ilClientIniFile;
	 	
	 	if(!$this->current_auth_mode = current($this->auth_modes))
	 	{
	 		return false;
	 	}
	 	next($this->auth_modes);
	 	switch($this->current_auth_mode)
	 	{
	 		case AUTH_LDAP:
	 			$ilLog->write(__METHOD__.': Current Authentication method is LDAP.');
				include_once 'Services/LDAP/classes/class.ilAuthLDAP.php';
				$this->auth = new ilAuthLDAP();
				break;
				
			case AUTH_RADIUS:
	 			$ilLog->write(__METHOD__.': Current Authentication method is RADIUS.');
				include_once('Services/Radius/classes/class.ilAuthRadius.php');
				$this->auth = new ilAuthRadius();
				break;
			case AUTH_LOCAL:
	 			$ilLog->write(__METHOD__.': Current Authentication method is ILIAS DB.');
				$auth_params = array(
											'dsn'		  => IL_DSN,
											'table'       => $ilClientIniFile->readVariable("auth", "table"),
											'usernamecol' => $ilClientIniFile->readVariable("auth", "usercol"),
											'passwordcol' => $ilClientIniFile->readVariable("auth", "passcol")
											);
				$this->auth = new Auth("DB", $auth_params,"",false);
				break;				
	 	}
	 	$this->auth->start();
	 	return true;
	}
	
}
?>