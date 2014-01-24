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
 * @classDescription Base class for all ILIAS PEAR container classes
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de
 * @version $id$
 *  
 * @ingroup ServicesAuthentication
 */
abstract class ilAuthContainerBase
{
	
	/**
	 * Called after successful login
	 * @return bool
	 * @param object $a_username
	 * @param object $a_auth
	 */
	public function loginObserver($a_username,$a_auth)
	{
		return true;
	}
	
	/** 
	 * Called after failed login
	 *
	 * @return bool
	 * @param string username
	 * @param object PEAR auth object
	 */
	public function failedLoginObserver($a_username,$a_auth)
	{
		return false;
	}
	
	/** 
	 * Called after check auth requests
	 * 
	 * @return bool
	 * @param string username
	 * @param object PEAR auth object
	 */
	public function checkAuthObserver($a_username,$a_auth)
	{
		return true;
	}

	/** 
	 * Called after logout
	 * 
	 * @return bool
	 * @param string username
	 * @param object PEAR auth object
	 */
	public function logoutObserver($a_username,$a_auth)
	{
		
	}

	/**
	 * Returns whether or not the auth container supports the verification of captchas
	 * This should be true for those auth methods, which are available in the default login form.
	 * @return bool
	 */
	public function supportsCaptchaVerification()
	{
		return false;
	}
}
?>