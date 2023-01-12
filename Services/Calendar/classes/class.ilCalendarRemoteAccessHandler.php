<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPServices;

/**
 * @classDescription Handles requests from external calendar applications
 * @author           Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup          ServicesCalendar
 */
class ilCalendarRemoteAccessHandler
{
    private ?ilCalendarAuthenticationToken $token_handler = null;
    protected ?Refinery $refinery = null;
    protected ?HTTPServices $http = null;
    protected ?ilLogger $logger = null;
    protected ?ilLanguage $lng = null;

    public function __construct()
    {
    }

    public function getTokenHandler(): ?ilCalendarAuthenticationToken
    {
        return $this->token_handler;
    }

    /**
     * Fetch client id, the chosen calendar...
     */
    public function parseRequest(): void
    {
        // before initialization: $_GET and $_COOKIE is required is unavoidable
        // in the moment.
        if ($_GET['client_id']) {
            $_COOKIE['ilClientId'] = $_GET['client_id'];
        } else {
            $path_info_components = explode('/', $_SERVER['PATH_INFO']);
            $_COOKIE['ilClientId'] = $path_info_components[1];
        }
    }

    public function handleRequest(): bool
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
            ilUtil::deliverData($this->getTokenHandler()->getIcal(), 'calendar.ics', 'text/calendar');
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
        ilUtil::deliverData($export->getExportString(), 'calendar.ics', 'text/calendar');
        exit;
    }

    protected function initTokenHandler(): void
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $token = '';
        if ($this->http->wrapper()->query()->has('token')) {
            $token = $this->http->wrapper()->query()->retrieve(
                'token',
                $this->refinery->kindlyTo()->string()
            );
        }
        $this->logger->info('Authentication token: ' . $token);
        $this->token_handler = new ilCalendarAuthenticationToken(
            ilCalendarAuthenticationToken::lookupUser($token),
            $token
        );
    }

    protected function initIlias()
    {
        include_once './Services/Context/classes/class.ilContext.php';
        ilContext::init(ilContext::CONTEXT_ICAL);

        include_once './Services/Authentication/classes/class.ilAuthFactory.php';
        ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CALENDAR_TOKEN);

        include_once './Services/Init/classes/class.ilInitialisation.php';
        ilInitialisation::initILIAS();

        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');
        $this->logger = $DIC->logger()->cal();
    }

    protected function initUser(): bool
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
