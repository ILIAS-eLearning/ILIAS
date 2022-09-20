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

/**
 * Auth credentials for lti oauth based authentication
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendCredentialsLTI extends ilAuthFrontendCredentials implements ilAuthCredentials
{
    public function __construct()
    {
        parent::__construct();
        // overwrite default lti logger
//        $this->setLogger($GLOBALS['DIC']->logger()->lti());
    }



    /**
     * Init credentials from request
     */
    public function initFromRequest(): void
    {
        global $DIC;
        $logger = ilLoggerFactory::getLogger('ltis');
        $logger->debug('New lti authentication request...');
        $user_id = '';
        if ($DIC->http()->wrapper()->post()->has('login_hint')) {
            $logger->debug('LTI 1.3 initiate login...');
            $user_id = $DIC->http()->wrapper()->post()->retrieve('login_hint', $DIC->refinery()->kindlyTo()->string());
            ilSession::set("lti13_initiate_login", $user_id);
        }
        if ($DIC->http()->wrapper()->post()->has('user_id')) {
            $user_id = $DIC->http()->wrapper()->post()->retrieve('user_id', $DIC->refinery()->kindlyTo()->string());
        }
        if (empty($user_id) && ilSession::has("lti13_initiate_login")) {
            $user_id = ilSession::get("lti13_initiate_login");
            ilSession::clear("lti13_initiate_login");
        }
        // ToDo: Error Handling
        // if (empty($user_id)) {}
        $this->setUsername($user_id);
    }
}
