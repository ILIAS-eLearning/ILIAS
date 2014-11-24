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

include_once './Services/Calendar/classes/class.ilCalendarAuthenticationToken.php';

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
	private $token_handler = null;	

	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
	}
	
	public function getTokenHandler()
	{
		return $this->token_handler;
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
		$this->initTokenHandler();
		
		if($this->getTokenHandler()->getIcal() and !$this->getTokenHandler()->isIcalExpired())
		{
			ilUtil::deliverData($this->getTokenHandler(),'calendar.ics','text/calendar','utf-8');
		}
		
		include_once './Services/Calendar/classes/Export/class.ilCalendarExport.php';
		include_once './Services/Calendar/classes/class.ilCalendarCategories.php';
		if($this->getTokenHandler()->getSelectionType() == ilCalendarAuthenticationToken::SELECTION_CALENDAR)
		{
			#$export = new ilCalendarExport(array($this->getTokenHandler()->getCalendar()));
			$cats = ilCalendarCategories::_getInstance();
			$cats->initialize(ilCalendarCategories::MODE_REMOTE_SELECTED, $this->getTokenHandler()->getCalendar());
			$export = new ilCalendarExport($cats->getCategories(true));
		}
		else
		{
			$cats = ilCalendarCategories::_getInstance();
			$cats->initialize(ilCalendarCategories::MODE_REMOTE_ACCESS);
			$export = new ilCalendarExport($cats->getCategories(true));
		}
		
		$export->export();
	
		$this->getTokenHandler()->setIcal($export->getExportString());
		$this->getTokenHandler()->storeIcal();
		
		$GLOBALS['ilAuth']->logout();
		ilUtil::deliverData($export->getExportString(),'calendar.ics','text/calendar','utf-8');
		#echo $export->getExportString();
		#echo nl2br($export->getExportString());
		
		#$fp = fopen('ilias.ics', 'w');
		#fwrite($fp,$export->getExportString());
		exit;
	}
	
	protected function initTokenHandler()
	{
		$this->token_handler = new ilCalendarAuthenticationToken(
			ilCalendarAuthenticationToken::lookupUser($_GET['token']),
			$_GET['token']
		);
		return true;
	}
	
	protected function initIlias()
	{
		include_once "Services/Context/classes/class.ilContext.php";
		ilContext::init(ilContext::CONTEXT_ICAL);
		
		include_once './Services/Authentication/classes/class.ilAuthFactory.php';
		ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CALENDAR_TOKEN);
		
		$_POST['username'] = 'cal_auth_token';
		$_POST['password'] = 'cal_auth_token';
		
		require_once("Services/Init/classes/class.ilInitialisation.php");
		ilInitialisation::initILIAS();
		
		$GLOBALS['lng']->loadLanguageModule('dateplaner');
	}
}
?>