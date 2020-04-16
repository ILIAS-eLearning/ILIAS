<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for authentication providers (radius, ldap, apache, ...)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
abstract class ilAuthProvider implements ilAuthProviderInterface
{
    const STATUS_UNDEFINED = 0;
    const STATUS_AUTHENTICATION_SUCCESS = 1;
    const STATUS_AUTHENTICATION_FAILED = 2;
    const STATUS_MIGRATION = 3;
    
    
    private $logger = null;

    private $credentials = null;
    
    private $status = self::STATUS_UNDEFINED;
    private $user_id = 0;
    /**
     * Constructor
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        $this->logger = ilLoggerFactory::getLogger('auth');
        $this->credentials = $credentials;
    }
    
    /**
     * Get logger
     * @return \ilLogger $logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * @return \ilAuthCredentials $credentials
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
    
    /**
     * Handle failed authentication
     * @param string $a_reason
     */
    protected function handleAuthenticationFail(ilAuthStatus $status, $a_reason)
    {
        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
        $status->setReason($a_reason);
        return false;
    }
}
