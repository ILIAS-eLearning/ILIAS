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
 * Auth status implementation
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthStatus
{
    private static $instance = null;
    
    private $logger = null;
    
    const STATUS_UNDEFINED = 1;
    const STATUS_AUTHENTICATED = 2;
    const STATUS_AUTHENTICATION_FAILED = 3;
    const STATUS_ACCOUNT_MIGRATION_REQUIRED = 4;
    const STATUS_CODE_ACTIVATION_REQUIRED = 5;
    
    private $status = self::STATUS_UNDEFINED;
    private $reason = '';
    private $translated_reason = '';
    private $auth_user_id = 0;
    
    
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->logger = ilLoggerFactory::getLogger('auth');
    }
    
    /**
     * Get status instance
     * @return \ilAuthStatus
     */
    public static function getInstance() : self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }
    
    /**
     * Get logger
     * @return \ilLogger
     */
    protected function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * Set auth status
     * @param int $a_status
     */
    public function setStatus($a_status)
    {
        $this->status = $a_status;
    }
    
    /**
     * Get status
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Set reason
     * @param string $a_reason A laguage key, which can be translated to an end user message
     */
    public function setReason($a_reason)
    {
        $this->reason = $a_reason;
    }
    
    /**
     * Set translated reason
     * @param string $a_reason
     */
    public function setTranslatedReason($a_reason)
    {
        $this->translated_reason = $a_reason;
    }
    
    /**
     * Get reason for authentication success, fail, migration...
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
    
    /**
     * Get translated reason
     */
    public function getTranslatedReason()
    {
        if (strlen($this->translated_reason)) {
            return $this->translated_reason;
        }
        return $GLOBALS['DIC']->language()->txt($this->getReason());
    }
    
    
    public function setAuthenticatedUserId($a_id)
    {
        $this->auth_user_id = $a_id;
    }
    
    /**
     * Get authenticated user id
     * @return int
     */
    public function getAuthenticatedUserId()
    {
        return $this->auth_user_id;
    }
}
