<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
        $this->setLogger($GLOBALS['DIC']->logger()->lti());
    }


    
    /**
     * Init credentials from request
     */
    public function initFromRequest() : void
    {
        $this->getLogger()->debug('New lti authentication request...');
        $this->getLogger()->dump($_REQUEST, ilLogLevel::DEBUG);
        
        $this->setUsername($_POST['user_id']);
    }
}
