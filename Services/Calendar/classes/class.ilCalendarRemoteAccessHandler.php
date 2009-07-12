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
 * @classDescription Handles requests from external calendar applications
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ingroup ServicesCalendar
 * 
 */
class ilCalendarRemoteAccessHandler
{
	
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Fetch client id, the chosen calendar...
	 * @return 
	 */
	public function parseRequest()
	{
		if($_GET['client_id'])
		{
			$_COOKIE['ilClientId'] = $_GET['client_id'];
			
		}
		else
		{
			$path_info_components = explode('/',$_SERVER['PATH_INFO']);
			$_COOKIE['ilClientId'] = $path_info_components[1];
		}
	}
	
	/**
	 * Handle Request
	 * @return 
	 */
	public function handleRequest()
	{
		$this->initIlias();
		
		include_once './Services/Calendar/classes/Export/class.ilCalendarExport.php';
		include_once './Services/Calendar/classes/class.ilCalendarCategories.php';
		
		$cats = ilCalendarCategories::_getInstance();
		$cats->initialize(ilCalendarCategories::MODE_REMOTE_ACCESS);
		$export = new ilCalendarExport($cats->getCategories());
		$export->export();

		echo $export->getExportString();
		exit;
	}
	
	protected function initIlias()
	{
		include_once './Services/Authentication/classes/class.ilAuthFactory.php';
		ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CALENDAR);
		
		include_once("Services/Init/classes/class.ilInitialisation.php");
		$GLOBALS['ilInit'] = new ilInitialisation();
		$GLOBALS['ilInit']->initILIAS('webdav');
		$GLOBALS['lng']->loadLanguageModule('dateplaner');
	}
}
