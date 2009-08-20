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
* @classDescription Factory for PEAR Auth frontend classes
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesAuthentication
*/
class ilAuthFactory
{
	/**
	 * @var int
	 * Web based authentication
	 */
	const CONTEXT_WEB 	= 1;

	/**
	 * @var int
	 * HTTP Auth used for WebDAV and CalDAV
	 * If a special handling for WebDAV or CalDAV is required
	 * overwrite ilAuthHTTP with ilAuthCalDAV and create new
	 * constants.
	 */
	const CONTEXT_HTTP 	= 2;
	
	
	/**
	 * @var int
	 * SOAP based authentication
	 */
	const CONTEXT_SOAP	= 3;
	
	/**
	 * @var int
	 * Maybe not required. Cron based authentication 
	 */
	const CONTEXT_CRON	= 4;
	
	/**
	 * @var int
	 * Maybe not required. Cron based authentication 
	 */
	const CONTEXT_CAS	= 5;
	
	/**
	 * @var int
	 * Maybe not required. HTTP based authentication for calendar access 
	 */
	const CONTEXT_CALENDAR	= 6;
	

	/**
	 * @var int
	 */
	private static $context = self::CONTEXT_WEB;
	
	/**
	 * @var array context specific options
	 */
	private static $context_options = array();
	
	/**
	 * 
	 * @return int current context
	 */
	public static function getContext()
	{
		return self::$context;
	}
	
	/**
	 * set context
	 * @param int $a_context
	 * @return 
	 */
	public static function setContext($a_context)
	{
		self::$context = $a_context;
	}
	
	/**
	 * set context specific options for 
	 * later use in factory.
	 * @return 
	 * @param object $a_context
	 * @param object $a_options
	 */
	public static function setContextOptions($a_context,$a_options)
	{
		self::$context_options[$a_context] = $a_options;
	}
	
	/**
	 * Get options for a specific context
	 * @return 
	 */
	public static function getContextOptions()
	{
		return self::$context_options[$a_context] ? 
			self::$context_options[$a_context] : 
			array();
	}
	
	
	/**
	 * The factory
	 * @param object	$container ilAuthContainerBase
	 * @param array		$options		
	 * @return object ilAuthContainerBase
	 */
	public static function factory(ilAuthContainerBase $deco)
	{
		$options = self::getContextOptions(self::getContext());
		
		switch(self::$context)
		{
			case self::CONTEXT_WEB:
				include_once './Services/Authentication/classes/class.ilAuthWeb.php';
				return new ilAuthWeb($deco,$options);
				
			case self::CONTEXT_HTTP:
				include_once './Services/Authentication/classes/class.ilAuthHTTP.php';
				return new ilAuthHTTP($deco,$options);

			case self::CONTEXT_SOAP:
				include_once './Services/WebServices/SOAP/classes/class.ilAuthSOAP.php';
				return new ilAuthSOAP($deco,$options);
				
			case self::CONTEXT_CAS:
				include_once './Services/CAS/classes/class.ilAuthCAS.php';
				return new ilAuthCAS($deco,$options);
				
			case self::CONTEXT_CALENDAR:
				include_once './Services/Calendar/classes/class.ilAuthCalendar.php';
				return new ilAuthCalendar($deco,$options);
				
			case self::CONTEXT_CRON:
				include_once './cron/classes/class.ilAuthCron.php';
				return new ilAuthCron($deco,$options);
		}
	}
}
?>