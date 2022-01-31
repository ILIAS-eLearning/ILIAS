<?php declare(strict_types=1);
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
 * @author           Stefan Meyer <smeyer.ilias@gmx.de>
 * @version          $Id$
 * @ingroup          ServicesCalendar
 */
class ilCalendarRemoteAccessHandler
{
    private ?ilCalendarAuthenticationToken $token_handler = null;

    protected ?ilLogger $logger = null;
    protected ?ilLanguage $lng = null;

    public function __construct()
    {
    }

    public function getTokenHandler() : ?ilCalendarAuthenticationToken
    {
        return $this->token_handler;
    }

    /**
     * Fetch client id, the chosen calendar...
     */
    public function parseRequest() : void
    {
        if ($_GET['client_id']) {
            $_COOKIE['ilClientId'] = $_GET['client_id'];
        } else {
            $path_info_components = explode('/', $_SERVER['PATH_INFO']);
            $_COOKIE['ilClientId'] = $path_info_components[1];
        }
    }

    public function handleRequest() : bool
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

    protected function initTokenHandler() : void
    {
        $this->logger->info('Authentication token: ' . $_GET['token']);
        $this->token_handler = new ilCalendarAuthenticationToken(
            ilCalendarAuthenticationToken::lookupUser($_GET['token']),
            $_GET['token']
        );
    }

    protected function initIlias()
    {
        ilContext::init(ilContext::CONTEXT_ICAL);

        ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CALENDAR_TOKEN);

        ilInitialisation::initILIAS();

        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');
        $this->logger = $DIC->logger()->cal();
    }

    protected function initUser() : bool
    {
        global $DIC;

        if (!$this->getTokenHandler() instanceof ilCalendarAuthenticationToken) {
            $this->logger->info('Initialisation of authentication token failed');
            return false;
        }
        if (!$this->getTokenHandler()->getUserId()) {
            $this->logger->info('No user id found for calendar synchronisation');
            return false;
        }
        if (!ilObjUser::_exists($this->getTokenHandler()->getUserId())) {
            $this->logger->notice('No valid user id found for calendar synchronisation');
            return false;
        }

        $GLOBALS['DIC']['ilAuthSession']->setAuthenticated(true, $this->getTokenHandler()->getUserId());
        ilInitialisation::initUserAccount();

        if (!$DIC->user() instanceof ilObjUser) {
            $this->logger->debug('No user object defined');
        } else {
            $this->logger->debug('Current user is: ' . $DIC->user()->getId());
        }
        return true;
    }
}
