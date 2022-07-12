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
 * Auth status implementation
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthStatus
{
    private static ?ilAuthStatus $instance = null;
    
    private ilLanguage $lng;

    public const STATUS_UNDEFINED = 1;
    public const STATUS_AUTHENTICATED = 2;
    public const STATUS_AUTHENTICATION_FAILED = 3;
    public const STATUS_ACCOUNT_MIGRATION_REQUIRED = 4;
    public const STATUS_CODE_ACTIVATION_REQUIRED = 5;
    
    private int $status = self::STATUS_UNDEFINED;
    private string $reason = '';
    private string $translated_reason = '';
    private int $auth_user_id = 0;
    
    
    
    /**
     * Constructor
     */
    private function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
    }
    
    /**
     * Get status instance
     */
    public static function getInstance() : ilAuthStatus
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilAuthStatus();
    }
    
    /**
     * Set auth status
     */
    public function setStatus(int $a_status) : void
    {
        $this->status = $a_status;
    }
    
    /**
     * Get status
     * @return int $status
     */
    public function getStatus() : int
    {
        return $this->status;
    }
    
    /**
     * Set reason
     * @param string $a_reason A laguage key, which can be translated to an end user message
     */
    public function setReason(string $a_reason) : void
    {
        $this->reason = $a_reason;
    }
    
    /**
     * Set translated reason
     */
    public function setTranslatedReason(string $a_reason) : void
    {
        $this->translated_reason = $a_reason;
    }
    
    /**
     * Get reason for authentication success, fail, migration...
     */
    public function getReason() : string
    {
        return $this->reason;
    }
    
    /**
     * Get translated reason
     */
    public function getTranslatedReason() : string
    {
        if ($this->translated_reason !== '') {
            return $this->translated_reason;
        }
        return $this->lng->txt($this->getReason());
    }
    
    
    public function setAuthenticatedUserId(int $a_id) : void
    {
        $this->auth_user_id = $a_id;
    }
    
    /**
     * Get authenticated user id
     */
    public function getAuthenticatedUserId() : int
    {
        return $this->auth_user_id;
    }
}
