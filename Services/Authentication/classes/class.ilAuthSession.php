<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/class.ilSession.php';

/**
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthSession
{
    const SESSION_AUTH_AUTHENTICATED = '_authsession_authenticated';
    const SESSION_AUTH_USER_ID = '_authsession_user_id';
    const SESSION_AUTH_EXPIRED = '_authsession_expired';
    
    private static $instance = null;
    
    /**
     * @var ilLogger
     */
    private $logger = null;
    
    private $id = '';
    private $user_id = 0;
    private $expired = false;
    private $authenticated = false;
    
    /**
     * Consctructor
     * @param \ilLogger
     */
    private function __construct(\ilLogger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Get instance
     * @param \ilLogger
     * @return ilAuthSession
     */
    public static function getInstance(\ilLogger $logger)
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self($logger);
    }
    
    /**
     * @return ilLogger
     */
    protected function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * Start auth session
     * @return boolean
     */
    public function init()
    {
        session_start();
        
        $this->setId(session_id());
        
        $user_id = (int) ilSession::get(self::SESSION_AUTH_USER_ID);

        if ($user_id) {
            $this->getLogger()->debug('Resuming old session for user: ' . $user_id);
            $this->setUserId(ilSession::get(self::SESSION_AUTH_USER_ID));
            $this->expired = (int) ilSession::get(self::SESSION_AUTH_EXPIRED);
            $this->authenticated = (int) ilSession::get(self::SESSION_AUTH_AUTHENTICATED);
            
            $this->validateExpiration();
        } else {
            $this->getLogger()->debug('Started new session.');
            $this->setUserId(0);
            $this->expired = false;
            $this->authenticated = false;
        }
        return true;
    }
    
    /**
     * Check if current session is valid (authenticated and not expired)
     * @return bool
     */
    public function isValid()
    {
        return !$this->isExpired() && $this->isAuthenticated();
    }
    
    /**
     * Regenerate id
     */
    public function regenerateId()
    {
        $old_session_id = session_id();
        session_regenerate_id(true);
        $this->setId(session_id());
        $this->getLogger()->info('Session regenerate id: [' . substr($old_session_id, 0, 5) . '] -> [' . substr($this->getId(), 0, 5) . ']');
    }
    
    /**
     * Logout user => stop session
     */
    public function logout()
    {
        $this->getLogger()->debug('Logout called for: ' . $this->getUserId());
        $this->setAuthenticated(false, 0);
        session_regenerate_id(true);
        session_destroy();
    }
    
    /**
     * Check if session is authenticated
     */
    public function isAuthenticated()
    {
        return $this->authenticated;
    }
    
    /**
     * Set authenticated
     * @param authentication status $a_status
     * @return type
     */
    public function setAuthenticated($a_status, $a_user_id)
    {
        $this->authenticated = $a_status;
        $this->user_id = $a_user_id;
        ilSession::set(self::SESSION_AUTH_AUTHENTICATED, $a_status);
        ilSession::set(self::SESSION_AUTH_USER_ID, (int) $a_user_id);
        $this->setExpired(false);
        if ($a_status) {
            $this->regenerateId();
        }
    }
    
    /**
     * Check if current is or was expired in last request.
     * @return type
     */
    public function isExpired()
    {
        return (bool) $this->expired;
    }
    
    /**
     * Set session expired
     * @param type $a_status
     */
    public function setExpired($a_status)
    {
        $this->expired = $a_status;
        ilSession::set(self::SESSION_AUTH_EXPIRED, (int) $a_status);
    }
    
    /**
     * Set authenticated user id
     * @param int $a_id
     */
    public function setUserId($a_id)
    {
        $this->user_id = $a_id;
    }
    
    /**
     * Get authenticated user id
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }
    
    /**
     * Check expired value of session
     * @return bool
     */
    protected function validateExpiration()
    {
        if ($this->isExpired()) {
            // keep status
            return false;
        }
        
        if (time() > ilSession::lookupExpireTime($this->getId())) {
            $this->setExpired(true);
            return false;
        }
        return true;
    }
    
    /**
     * Set id
     * @param string $a_id
     */
    protected function setId($a_id)
    {
        $this->id = $a_id;
    }
    
    /**
     * get session id
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
