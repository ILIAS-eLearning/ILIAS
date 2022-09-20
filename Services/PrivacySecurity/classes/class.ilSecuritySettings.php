<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */


/**
 * Singleton class that stores all security settings
 * @author  Roland Küstermann <roland@kuestermann.com>
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup Services/PrivacySecurity
 */
class ilSecuritySettings
{
    public static int $SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS = 1;
    public static int $SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE = 2;
    public static int $SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE = 3;

    public const SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH = 4;
    public const SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH = 5;
    public const SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE = 6;
    public const SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS = 7;
    public const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN1 = 11;
    public const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2 = 8;
    public const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3 = 9;
    public const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH = 10;

    private static ?self $instance = null;
    private ilDBInterface $db;
    private ilSetting $settings;
    private ilRbacReview $review;
    protected ilHTTPS $https;

    private bool $https_enable;

    public const DEFAULT_PASSWORD_CHARS_AND_NUMBERS_ENABLED = true;
    public const DEFAULT_PASSWORD_SPECIAL_CHARS_ENABLED = false;
    public const DEFAULT_PASSWORD_MIN_LENGTH = 8;
    public const DEFAULT_PASSWORD_MAX_LENGTH = 0;
    public const DEFAULT_PASSWORD_MAX_AGE = 90;
    public const DEFAULT_LOGIN_MAX_ATTEMPTS = 5;

    public const DEFAULT_PASSWORD_CHANGE_ON_FIRST_LOGIN_ENABLED = false;
    public const DEFAULT_PREVENT_SIMULTANEOUS_LOGINS = false;

    private bool $password_chars_and_numbers_enabled = self::DEFAULT_PASSWORD_CHARS_AND_NUMBERS_ENABLED;
    private bool $password_special_chars_enabled = self::DEFAULT_PASSWORD_SPECIAL_CHARS_ENABLED;
    private int $password_min_length = self::DEFAULT_PASSWORD_MIN_LENGTH;
    private int $password_max_length = self::DEFAULT_PASSWORD_MAX_LENGTH;
    private int $password_max_age = self::DEFAULT_PASSWORD_MAX_AGE;
    private int $password_ucase_chars_num = 0;
    private int $password_lcase_chars_num = 0;
    private int $login_max_attempts = self::DEFAULT_LOGIN_MAX_ATTEMPTS;
    private bool $password_must_not_contain_loginname = false;

    private bool $password_change_on_first_login_enabled = self::DEFAULT_PASSWORD_CHANGE_ON_FIRST_LOGIN_ENABLED;
    private bool $prevent_simultaneous_logins = self::DEFAULT_PREVENT_SIMULTANEOUS_LOGINS;

    private bool $protect_admin_role = false;

    /**
     * Private constructor: use _getInstance()
     */
    private function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
        $this->review = $DIC->rbac()->review();
        $this->https = $DIC['https'];

        $this->read();
    }

    /**
     * Get instance of ilSecuritySettings
     * @return ilSecuritySettings  instance
     * @access public
     */
    public static function _getInstance(): ilSecuritySettings
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * set if the passwords have to contain
     * characters and numbers
     */
    public function setPasswordCharsAndNumbersEnabled(bool $a_chars_and_numbers_enabled): void
    {
        $this->password_chars_and_numbers_enabled = $a_chars_and_numbers_enabled;
    }

    /**
     * get boolean if the passwords have to contain
     * characters and numbers
     */
    public function isPasswordCharsAndNumbersEnabled(): bool
    {
        return $this->password_chars_and_numbers_enabled;
    }

    /**
     * set if the passwords have to contain
     * special characters
     */
    public function setPasswordSpecialCharsEnabled(bool $a_password_special_chars_enabled): void
    {
        $this->password_special_chars_enabled = $a_password_special_chars_enabled;
    }

    /**
     * get boolean if the passwords have to contain
     * special characters
     */
    public function isPasswordSpecialCharsEnabled(): bool
    {
        return $this->password_special_chars_enabled;
    }

    /**
     * set the minimum length for passwords
     */
    public function setPasswordMinLength(int $a_password_min_length): void
    {
        $this->password_min_length = $a_password_min_length;
    }

    /**
     * get the minimum length for passwords
     */
    public function getPasswordMinLength(): int
    {
        return $this->password_min_length;
    }

    /**
     * set the maximum length for passwords
     */
    public function setPasswordMaxLength(int $a_password_max_length): void
    {
        $this->password_max_length = $a_password_max_length;
    }

    /**
     * get the maximum length for passwords
     */
    public function getPasswordMaxLength(): int
    {
        return $this->password_max_length;
    }

    /**
     * set the maximum password age
     */
    public function setPasswordMaxAge(int $a_password_max_age): void
    {
        $this->password_max_age = $a_password_max_age;
    }

    /**
     * get the maximum password age
     */
    public function getPasswordMaxAge(): int
    {
        return $this->password_max_age;
    }

    /**
     * set the maximum count of login attempts
     */
    public function setLoginMaxAttempts(int $a_login_max_attempts): void
    {
        $this->login_max_attempts = $a_login_max_attempts;
    }

    /**
     * get the maximum count of login attempts
     */
    public function getLoginMaxAttempts(): int
    {
        return $this->login_max_attempts;
    }

    /**
     * Enable https for certain scripts
     */
    public function setHTTPSEnabled(bool $value): void
    {
        $this->https_enable = $value;
    }

    /**
     * read access to https enabled property
     */
    public function isHTTPSEnabled(): bool
    {
        return $this->https_enable;
    }

    /**
     * set if the passwords have to be changed by users
     * on first login
     */
    public function setPasswordChangeOnFirstLoginEnabled(bool $a_password_change_on_first_login_enabled): void
    {
        $this->password_change_on_first_login_enabled = $a_password_change_on_first_login_enabled;
    }

    /**
     * get boolean if the passwords have to be changed by users
     * on first login
     */
    public function isPasswordChangeOnFirstLoginEnabled(): bool
    {
        return $this->password_change_on_first_login_enabled;
    }

    public function isAdminRoleProtected(): bool
    {
        return (bool) $this->protect_admin_role;
    }

    public function protectedAdminRole(bool $a_stat): void
    {
        $this->protect_admin_role = $a_stat;
    }

    /**
     * Check if the administrator role is accessible for a specific user
     */
    public function checkAdminRoleAccessible(int $a_usr_id): bool
    {
        if (!$this->isAdminRoleProtected()) {
            return true;
        }
        if ($this->review->isAssigned($a_usr_id, SYSTEM_ROLE_ID)) {
            return true;
        }
        return false;
    }

    /**
     * Save settings
     */
    public function save(): void
    {
        $this->settings->set('https', (string) $this->isHTTPSEnabled());

        $this->settings->set('ps_password_chars_and_numbers_enabled', (string) $this->isPasswordCharsAndNumbersEnabled());
        $this->settings->set('ps_password_special_chars_enabled', (string) $this->isPasswordSpecialCharsEnabled());
        $this->settings->set('ps_password_min_length', (string) $this->getPasswordMinLength());
        $this->settings->set('ps_password_max_length', (string) $this->getPasswordMaxLength());
        $this->settings->set('ps_password_max_age', (string) $this->getPasswordMaxAge());
        $this->settings->set('ps_login_max_attempts', (string) $this->getLoginMaxAttempts());
        $this->settings->set('ps_password_uppercase_chars_num', (string) $this->getPasswordNumberOfUppercaseChars());
        $this->settings->set('ps_password_lowercase_chars_num', (string) $this->getPasswordNumberOfLowercaseChars());
        $this->settings->set(
            'ps_password_must_not_contain_loginame',
            (string) $this->getPasswordMustNotContainLoginnameStatus()
        );

        $this->settings->set(
            'ps_password_change_on_first_login_enabled',
            (string) $this->isPasswordChangeOnFirstLoginEnabled()
        );
        $this->settings->set('ps_prevent_simultaneous_logins', (string) $this->isPreventionOfSimultaneousLoginsEnabled());
        $this->settings->set('ps_protect_admin', (string) $this->isAdminRoleProtected());
    }

    /**
     * read settings
     * @access private
     * @param
     */
    private function read(): void
    {
        $query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data " .
            "WHERE tree.parent = " . $this->db->quote(SYSTEM_FOLDER_ID, 'integer') . " " .
            "AND object_data.type = 'ps' " .
            "AND object_reference.ref_id = tree.child " .
            "AND object_reference.obj_id = object_data.obj_id";
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        $this->https_enable = (bool) $this->settings->get('https', null);

        $this->password_chars_and_numbers_enabled = (bool) $this->settings->get(
            'ps_password_chars_and_numbers_enabled',
            (string) self::DEFAULT_PASSWORD_CHARS_AND_NUMBERS_ENABLED
        );
        $this->password_special_chars_enabled = (bool) $this->settings->get(
            'ps_password_special_chars_enabled',
            (string) self::DEFAULT_PASSWORD_SPECIAL_CHARS_ENABLED
        );
        $this->password_min_length = (int) $this->settings->get(
            'ps_password_min_length',
            (string) self::DEFAULT_PASSWORD_MIN_LENGTH
        );
        $this->password_max_length = (int) $this->settings->get(
            'ps_password_max_length',
            (string) self::DEFAULT_PASSWORD_MAX_LENGTH
        );
        $this->password_max_age = (int) $this->settings->get('ps_password_max_age', (string) self::DEFAULT_PASSWORD_MAX_AGE);
        $this->login_max_attempts = (int) $this->settings->get(
            'ps_login_max_attempts',
            (string) self::DEFAULT_LOGIN_MAX_ATTEMPTS
        );
        $this->password_ucase_chars_num = (int) $this->settings->get('ps_password_uppercase_chars_num', "0");
        $this->password_lcase_chars_num = (int) $this->settings->get('ps_password_lowercase_chars_num', "0");
        $this->password_must_not_contain_loginname = (bool) $this->settings->get(
            'ps_password_must_not_contain_loginame',
            null
        );
        $this->password_change_on_first_login_enabled = (bool) $this->settings->get(
            'ps_password_change_on_first_login_enabled',
            (string) self::DEFAULT_PASSWORD_CHANGE_ON_FIRST_LOGIN_ENABLED
        );
        $this->prevent_simultaneous_logins = (bool) $this->settings->get(
            'ps_prevent_simultaneous_logins',
            (string) self::DEFAULT_PREVENT_SIMULTANEOUS_LOGINS
        );
        $this->protect_admin_role = (bool) $this->settings->get('ps_protect_admin', (string) $this->protect_admin_role);
    }

    /**
     * validate settings
     * @param ilPropertyFormGUI|null $a_form
     * @return int|null 0, if everything is ok, an error code otherwise
     */
    public function validate(ilPropertyFormGUI $a_form = null): ?int
    {
        $code = null;

        if ($this->isHTTPSEnabled()) {
            if (!$this->https->checkHTTPS()) {
                $code = ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE;
                if (!$a_form) {
                    return $code;
                } else {
                    $a_form->getItemByPostVar('https_enabled')
                           ->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
                }
            }
        }

        if ($this->getPasswordMinLength() < 0) {
            $code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH;
            if (!$a_form) {
                return $code;
            } else {
                $a_form->getItemByPostVar('password_min_length')
                       ->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
            }
        }

        if ($this->getPasswordMaxLength() < 0) {
            $code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH;
            if (!$a_form) {
                return $code;
            } else {
                $a_form->getItemByPostVar('password_max_length')
                       ->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
            }
        }

        $password_min_length = 1;
        $password_min_length_error_code = null;

        if ($this->getPasswordNumberOfUppercaseChars() > 0 || $this->getPasswordNumberOfLowercaseChars() > 0) {
            $password_min_length = 0;
            if ($this->getPasswordNumberOfUppercaseChars() > 0) {
                $password_min_length += $this->getPasswordNumberOfUppercaseChars();
            }
            if ($this->getPasswordNumberOfLowercaseChars() > 0) {
                $password_min_length += $this->getPasswordNumberOfLowercaseChars();
            }
            $password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN1;
        }

        if ($this->isPasswordCharsAndNumbersEnabled()) {
            $password_min_length++;
            $password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2;

            if ($this->isPasswordSpecialCharsEnabled()) {
                $password_min_length++;
                $password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3;
            }
        } elseif ($password_min_length > 1 && $this->isPasswordSpecialCharsEnabled()) {
            $password_min_length++;
            $password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3;
        }

        if ($this->getPasswordMinLength() > 0 && $this->getPasswordMinLength() < $password_min_length) {
            $code = $password_min_length_error_code;
            if (!$a_form) {
                return $code;
            } else {
                $a_form->getItemByPostVar('password_min_length')
                       ->setAlert(sprintf(ilObjPrivacySecurityGUI::getErrorMessage($code), $password_min_length));
            }
        }
        if ($this->getPasswordMaxLength() > 0 && $this->getPasswordMaxLength() < $this->getPasswordMinLength()) {
            $code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH;
            if (!$a_form) {
                return $code;
            } else {
                $a_form->getItemByPostVar('password_max_length')
                       ->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
            }
        }
        if ($this->getPasswordMaxAge() < 0) {
            $code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE;
            if (!$a_form) {
                return $code;
            } else {
                $a_form->getItemByPostVar('password_max_age')
                       ->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
            }
        }

        if ($this->getLoginMaxAttempts() < 0) {
            $code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS;
            if (!$a_form) {
                return $code;
            } else {
                $a_form->getItemByPostVar('login_max_attempts')
                       ->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
            }
        }

        /*
         * todo: have to check for local auth if first login password change is enabled??
         * than: add errorcode
         */

        if (!$a_form) {
            return 0;
        } else {
            return $code;
        }
    }

    /**
     * Prevention of simultaneous logins with the same account
     * @return bool true, if prevention of simultaneous logins with the same account is enabled, false otherwise
     */
    public function isPreventionOfSimultaneousLoginsEnabled(): bool
    {
        return $this->prevent_simultaneous_logins;
    }

    /**
     * Enable/Disable prevention of simultaneous logins with the same account
     */
    public function setPreventionOfSimultaneousLogins(bool $value): void
    {
        $this->prevent_simultaneous_logins = $value;
    }

    /**
     * Set number of uppercase characters required
     */
    public function setPasswordNumberOfUppercaseChars(int $password_ucase_chars_num): void
    {
        $this->password_ucase_chars_num = $password_ucase_chars_num;
    }

    /**
     * Returns number of uppercase characters required
     */
    public function getPasswordNumberOfUppercaseChars(): int
    {
        return $this->password_ucase_chars_num;
    }

    /**
     * Set number of lowercase characters required
     */
    public function setPasswordNumberOfLowercaseChars(int $password_lcase_chars_num): void
    {
        $this->password_lcase_chars_num = $password_lcase_chars_num;
    }

    /**
     * Returns number of lowercase characters required
     */
    public function getPasswordNumberOfLowercaseChars(): int
    {
        return $this->password_lcase_chars_num;
    }

    /**
     * Set whether the password must not contain the loginname or not
     */
    public function setPasswordMustNotContainLoginnameStatus($status): void
    {
        $this->password_must_not_contain_loginname = (bool) $status;
    }

    /**
     * Return whether the password must not contain the loginname or not
     */
    public function getPasswordMustNotContainLoginnameStatus(): bool
    {
        return $this->password_must_not_contain_loginname;
    }
}
