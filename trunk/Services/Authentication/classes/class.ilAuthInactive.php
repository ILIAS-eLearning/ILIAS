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

include_once('Auth/Auth.php');
include_once('Auth/Container.php');
include_once('./Services/Authentication/classes/class.ilAuthUtils.php');

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesAuthentication 
*/
class ilAuthInactive extends Auth
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct()
	{
		parent::__construct(new Auth_Container());
	}
	
	/**
	 * start
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function start()
	{
		$this->status = AUTH_MODE_INACTIVE;
		$this->logout();
		return false;
	}
	
	/**
	 * get status
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getStatus()
	{
		return AUTH_MODE_INACTIVE;
	}

}
?>