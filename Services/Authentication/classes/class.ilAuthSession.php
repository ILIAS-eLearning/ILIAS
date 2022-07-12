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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAuthSession
{
    private const SESSION_AUTH_AUTHENTICATED = '_authsession_authenticated';
    private const SESSION_AUTH_USER_ID = '_authsession_user_id';
    private const SESSION_AUTH_EXPIRED = '_authsession_expired';
    
    private static ?ilAuthSession $instance = null;
    
    private ilLogger $logger;
    
    private string $id = '';
    private int $user_id = 0;
    private bool $expired = false;
    private bool $authenticated = false;
    
    private function __construct(\ilLogger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Get instance
     * @param \ilLogger
     * @return ilAuthSession
     */
    public static function getInstance(\ilLogger $logger) : ilAuthSession
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self($logger);
    }
    
    /**
     * @return ilLogger
     */
    protected function getLogger() : ilLogger
    {
        return $this->logger;
    }
    
    /**
     * Start auth session
     */
    public function init() : bool
    {
        session_start();
        
        $this->setId(session_id());
        
        $user_id = (int) ilSession::get(self::SESSION_AUTH_USER_ID);

        if ($user_id) {
            $this->getLogger()->debug('Resuming old session for user: ' . $user_id);
            $this->setUserId((int) ilSession::get(self::SESSION_AUTH_USER_ID));
            $this->expired = (bool) ilSession::get(self::SESSION_AUTH_EXPIRED);
            $this->authenticated = (bool) ilSession::get(self::SESSION_AUTH_AUTHENTICATED);
            
            $this->validateExpiration();
        } else {
            $this->getLogger()->debug('Started new session.');
            $this->setUserId(ANONYMOUS_USER_ID);
            $this->expired = false;
            $this->authenticated = false;
        }
        return true;
    }
    
    /**
     * Check if current session is valid (authenticated and not expired)
     */
    public function isValid() : bool
    {
        return !$this->isExpired() && $this->isAuthenticated();
    }
    
    /**
     * Regenerate id
     */
    public function regenerateId() : void
    {
        $old_session_id = session_id();
        session_regenerate_id(true);
        $this->setId(session_id());
        $this->getLogger()->info('Session regenerate id: [' . substr($old_session_id, 0, 5) . '] -> [' . substr($this->getId(), 0, 5) . ']');
    }
    
    /**
     * Logout user => stop session
     */
    public function logout() : void
    {
        $this->getLogger()->debug('Logout called for: ' . $this->getUserId());
        session_regenerate_id(true);
        session_destroy();

        $this->init();
        $this->setAuthenticated(true, ANONYMOUS_USER_ID);
    }
    
    /**
     * Check if session is authenticated
     */
    public function isAuthenticated() : bool
    {
        return $this->authenticated;
    }
    
    /**
     * Set authenticated
     */
    public function setAuthenticated(bool $a_status, int $a_user_id) : void
    {
        $this->authenticated = $a_status;
        $this->user_id = $a_user_id;
        ilSession::set(self::SESSION_AUTH_AUTHENTICATED, $a_status);
        ilSession::set(self::SESSION_AUTH_USER_ID, $a_user_id);
        $this->setExpired(false);
        if ($a_status) {
            $this->regenerateId();
        }
    }
    
    /**
     * Check if current is or was expired in last request.
     */
    public function isExpired() : bool
    {
        return $this->expired;
    }
    
    /**
     * Set session expired
     */
    public function setExpired(bool $a_status) : void
    {
        $this->expired = $a_status;
        ilSession::set(self::SESSION_AUTH_EXPIRED, (int) $a_status);
    }
    
    /**
     * Set authenticated user id
     */
    public function setUserId(int $a_id) : void
    {
        $this->user_id = $a_id;
    }
    
    /**
     * Get authenticated user id
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }
    
    /**
     * Check expired value of session
     */
    protected function validateExpiration() : bool
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
     */
    protected function setId(string $a_id) : void
    {
        $this->id = $a_id;
    }
    
    /**
     * get session id
     */
    public function getId() : string
    {
        return $this->id;
    }
}
