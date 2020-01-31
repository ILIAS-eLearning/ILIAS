<?php

/**
 * This class contains some functions from the old ilDAVServer.
 * Sadly I wasn't able to refactor all of it. Some functions are still used in other classes. Will be refactored
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 *
 * TODO: Check for refactoring potential
 */
class ilWebDAVUtil
{
    private static $instance = null;

    private $pwd_instruction = null;

    /**
     * Singleton constructor
     * @return
     */
    private function __construct()
    {
    }

    /**
     * Get singleton instance
     * @return object ilDAVUtils
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilWebDAVUtil();
    }

    /**
     *  --> deleting this method depends on the "local password" discussion
     *
     * @return
     */
    public function isLocalPasswordInstructionRequired()
    {
        global $DIC;
        $ilUser = $DIC->user();

        if ($this->pwd_instruction !== null) {
            return $this->pwd_instruction;
        }
        include_once './Services/Authentication/classes/class.ilAuthUtils.php';
        $status = ilAuthUtils::supportsLocalPasswordValidation($ilUser->getAuthMode(true));
        if ($status != ilAuthUtils::LOCAL_PWV_USER) {
            return $this->pwd_instruction = false;
        }
        // Check if user has local password
        return $this->pwd_instruction = (bool) !strlen($ilUser->getPasswd());
    }
}
