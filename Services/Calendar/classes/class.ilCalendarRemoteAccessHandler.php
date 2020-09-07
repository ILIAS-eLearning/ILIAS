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
    
    /**
     * @return ilCalendarAuthenticationHandler
     */
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
        if ($_GET['client_id']) {
            $_COOKIE['ilClientId'] = $_GET['client_id'];
        } else {
            $path_info_components = explode('/', $_SERVER['PATH_INFO']);
            $_COOKIE['ilClientId'] = $path_info_components[1];
        }
    }
    
    /**
     * Handle Request
     * @return
     */
    public function handleRequest()
    {
        session_name('ILCALSESSID');
        $this->initIlias();
        $logger = $GLOBALS['DIC']->logger()->cal();
        $this->initTokenHandler();
        
        if (!$this->initUser()) {
            $logger->warning('Calendar token is invalid. Authentication failed.');
            return false;
        }
        
        if ($this->getTokenHandler()->getIcal() and !$this->getTokenHandler()->isIcalExpired()) {
            $GLOBALS['DIC']['ilAuthSession']->logout();
            ilUtil::deliverData($this->getTokenHandler(), 'calendar.ics', 'text/calendar', 'utf-8');
            exit;
        }
        
        include_once './Services/Calendar/classes/Export/class.ilCalendarExport.php';
        include_once './Services/Calendar/classes/class.ilCalendarCategories.php';
        if ($this->getTokenHandler()->getSelectionType() == ilCalendarAuthenticationToken::SELECTION_CALENDAR) {
            #$export = new ilCalendarExport(array($this->getTokenHandler()->getCalendar()));
            $cats = ilCalendarCategories::_getInstance();
            $cats->initialize(ilCalendarCategories::MODE_REMOTE_SELECTED, $this->getTokenHandler()->getCalendar());
            $export = new ilCalendarExport($cats->getCategories(true));
        } else {
            $cats = ilCalendarCategories::_getInstance();
            $cats->initialize(ilCalendarCategories::MODE_REMOTE_ACCESS);
            $export = new ilCalendarExport($cats->getCategories(true));
        }
        
        $export->export();
    
        $this->getTokenHandler()->setIcal($export->getExportString());
        $this->getTokenHandler()->storeIcal();

        $GLOBALS['DIC']['ilAuthSession']->logout();
        ilUtil::deliverData($export->getExportString(), 'calendar.ics', 'text/calendar', 'utf-8');
        exit;
    }
    
    protected function initTokenHandler()
    {
        $GLOBALS['DIC']->logger()->cal()->info('Authentication token: ' . $_GET['token']);
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
        
        require_once("Services/Init/classes/class.ilInitialisation.php");
        ilInitialisation::initILIAS();
        
        $GLOBALS['DIC']['lng']->loadLanguageModule('dateplaner');
    }
    
    /**
     * Init user
     * @return boolean
     */
    protected function initUser()
    {
        if (!$this->getTokenHandler() instanceof ilCalendarAuthenticationToken) {
            $GLOBALS['DIC']->logger()->cal()->info('Initialisation of authentication token failed');
            return false;
        }
        if (!$this->getTokenHandler()->getUserId()) {
            $GLOBALS['DIC']->logger()->cal()->info('No user id found for calendar synchronisation');
            return false;
        }
        include_once './Services/User/classes/class.ilObjUser.php';
        if (!ilObjUser::_exists($this->getTokenHandler()->getUserId())) {
            $GLOBALS['DIC']->logger()->cal()->notice('No valid user id found for calendar synchronisation');
            return false;
        }
        
        include_once './Services/Init/classes/class.ilInitialisation.php';
        $GLOBALS['DIC']['ilAuthSession']->setAuthenticated(true, $this->getTokenHandler()->getUserId());
        ilInitialisation::initUserAccount();
        
        if (!$GLOBALS['DIC']->user() instanceof ilObjUser) {
            $GLOBALS['DIC']->logger()->cal()->debug('no user object defined');
        } else {
            $GLOBALS['DIC']->logger()->cal()->debug('Current user is: ' . $GLOBALS['DIC']->user()->getId());
        }
        return true;
    }
}
