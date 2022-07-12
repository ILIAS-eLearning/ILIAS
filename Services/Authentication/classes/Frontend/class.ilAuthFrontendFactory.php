<?php declare(strict_types=1);

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
 * Factory for auth frontend classes.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthFrontendFactory
{
    private const CONTEXT_UNDEFINED = 0;
    
    // authentication with id and password. Used for standard form based authentication
    // soap auth (login) but not for (CLI (cron)?) and HTTP basic authentication
    public const CONTEXT_STANDARD_FORM = 2;
    
    // CLI context for cron
    public const CONTEXT_CLI = 3;
    
    // Rest soap context
    public const CONTEXT_WS = 4;
    
    // http auth
    public const CONTEXT_HTTP = 5;
    
    
    private int $context = self::CONTEXT_UNDEFINED;
    private ilLogger $logger;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
    }
    
    /**
     * Set context for following authentication requests
     */
    public function setContext(int $a_context) : void
    {
        $this->context = $a_context;
    }
    
    /**
     * Get context
     */
    public function getContext() : int
    {
        return $this->context;
    }

    public function getFrontend(ilAuthSession $session, ilAuthStatus $status, ilAuthCredentials $credentials, array $providers) : ?ilAuthFrontendInterface
    {
        switch ($this->getContext()) {
            case self::CONTEXT_CLI:
                $this->logger->debug('Init auth frontend with standard auth context');
                $frontend = new ilAuthFrontendCLI(
                    $session,
                    $status,
                    $credentials,
                    $providers
                );
                return $frontend;
                
            case self::CONTEXT_WS:
                $this->logger->debug('Init auth frontend with webservice auth context');
                $frontend = new ilAuthFrontendWS(
                    $session,
                    $status,
                    $credentials,
                    $providers
                );
                return $frontend;
                
            case self::CONTEXT_STANDARD_FORM:
                $this->logger->debug('Init auth frontend with standard auth context');
                $frontend = new ilAuthStandardFormFrontend(
                    $session,
                    $status,
                    $credentials,
                    $providers
                );
                return $frontend;
                
            case self::CONTEXT_HTTP:
                $this->logger->debug('Init auth frontend with http basic auth context');
                $frontend = new ilAuthFrontendHTTP(
                    $session,
                    $status,
                    $credentials,
                    $providers
                );
                return $frontend;
            
            case self::CONTEXT_UNDEFINED:
                $this->logger->error('Trying to init auth with empty context');
                break;
        }
        return null;
    }
}
