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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP 
*/
class ilAuthLDAPLogObserver extends Log_observer
{
	protected $log;
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int log level PEAR_AUTH_INFO | PEAR_AUTH_DEBUG
	 * 
	 */
	public function __construct($a_level)
	{
		global $ilLog;
		
		$this->log = $ilLog;
		parent::__construct($a_level);		
	}


	/**
	 * Notify
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function notify($a_event)
	{
        $this->log->write('PEAR LDAP: '.$a_event['message']);
        $this->messages[] = $a_event;
    }


}
?>