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


include_once 'Auth/HTTP.php';

/** 
* Base class for ilAuth, ilAuthHTTP ....
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesAuthentication
*/
class ilAuthHTTP extends Auth_HTTP
{
   
	/**
	 * Returns true, if the current auth mode allows redirection to e.g 
	 * to loginScreen, public section... 
	 * @return 
	 */
	public function supportsRedirects()
	{
		return false;
	} 

    /**
     * Constructor
     * 
	 * @param object Auth_ContainerBase
	 * @param array	further options Not used in the moment
     */
    public function __construct($container, $a_options = array())
    {
		$a_options['sessionSharing'] = false;

    	parent::__construct($container,$a_options);
		$this->setSessionName("_authhttp".md5(CLIENT_ID));
		$this->setRealm(CLIENT_ID);

		$this->initAuth();
    }
	
	/**
	 * Overwritten to allow passwordless mount-instructions
	 * @return 
	 */
	public function assignData()
	{
		if(isset($_GET['mount-instructions']))
		{
			$GLOBALS['ilLog']->write('Trying authentication as anonymous for displaying mount instructions');
			$this->username = 'anonymous';
			$this->password = 'anonymous';
		}
		else
		{
			parent::assignData();
		}
		
	}
	
	/**
	 * Failed login. => Draw login (HTTP 401)
	 * @param object $a_username
	 * @param object $a_auth
	 * @return 
	 */
	protected function failedLoginObserver($a_username, $a_auth)
	{
		// First, call parent observer and
		if(!parent::failedLoginObserver($a_username,$a_auth))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': HTTP authentication failed. Sending status 401');
			$this->drawLogin($a_username);
			return false;
		}
		return false;
	}
}

?>