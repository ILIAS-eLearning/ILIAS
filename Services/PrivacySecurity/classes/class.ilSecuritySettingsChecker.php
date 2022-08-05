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

class ilSecuritySettingsChecker
{
    
    /**
     * @deprecated use ilSecuritySettings
     */
    public static function isPassword(string $a_passwd, ?string &$customError = null) : bool
    {
        global $DIC;
        
        $lng = $DIC->language();
        
        $security = ilSecuritySettings::_getInstance();
        
        // check if password is empty
        if (empty($a_passwd)) {
            $customError = $lng->txt('password_empty');
            return false;
        }
        
        $isPassword = true;
        $errors = [];
        
        // check if password to short
        if ($security->getPasswordMinLength() > 0 && strlen($a_passwd) < $security->getPasswordMinLength()) {
            $errors[] = sprintf($lng->txt('password_to_short'), $security->getPasswordMinLength());
            $isPassword = false;
        }
        
        // check if password not to long
        // Hmmmmm, maybe we should discuss this limitation. In my opinion it is stupid to limit the password length ;-). There should only be a technical limitation (field size in database).
        if ($security->getPasswordMaxLength() > 0 && strlen($a_passwd) > $security->getPasswordMaxLength()) {
            $errors[] = sprintf($lng->txt('password_to_long'), $security->getPasswordMaxLength());
            $isPassword = false;
        }
        
        // if password must contains Chars and Numbers
        if ($security->isPasswordCharsAndNumbersEnabled()) {
            $hasCharsAndNumbers = true;
            
            // check password for existing chars
            if (!preg_match('/[A-Za-z]+/', $a_passwd)) {
                $hasCharsAndNumbers = false;
            }
            
            // check password for existing numbers
            if (!preg_match('/[0-9]+/', $a_passwd)) {
                $hasCharsAndNumbers = false;
            }
            
            if (!$hasCharsAndNumbers) {
                $errors[] = $lng->txt('password_must_chars_and_numbers');
                $isPassword = false;
            }
        }
        
        if ($security->getPasswordNumberOfUppercaseChars() > 0) {
            if (ilStr::strLen($a_passwd) - ilStr::strLen(
                preg_replace('/[A-Z]/', '', $a_passwd)
            ) < $security->getPasswordNumberOfUppercaseChars()) {
                $errors[] = sprintf(
                    $lng->txt('password_must_contain_ucase_chars'),
                    $security->getPasswordNumberOfUppercaseChars()
                );
                $isPassword = false;
            }
        }
        
        if ($security->getPasswordNumberOfLowercaseChars() > 0) {
            if (ilStr::strLen($a_passwd) - ilStr::strLen(
                preg_replace('/[a-z]/', '', $a_passwd)
            ) < $security->getPasswordNumberOfLowercaseChars()) {
                $errors[] = sprintf(
                    $lng->txt('password_must_contain_lcase_chars'),
                    $security->getPasswordNumberOfLowercaseChars()
                );
                $isPassword = false;
            }
        }
        
        // if password must contains Special-Chars
        if ($security->isPasswordSpecialCharsEnabled()) {
            // check password for existing special-chars
            if (!preg_match(self::getPasswordValidChars(true, true), $a_passwd)) {
                $errors[] = $lng->txt('password_must_special_chars');
                $isPassword = false;
            }
        }
        
        // ensure password matches the positive list of chars/special-chars
        if (!preg_match(self::getPasswordValidChars(), $a_passwd)) {
            $errors[] = $lng->txt('password_contains_invalid_chars');
            $isPassword = false;
        }
        
        // build custom error message
        if (count($errors) == 1) {
            $customError = $errors[0];
        } elseif (count($errors) > 1) {
            $customError = $lng->txt('password_multiple_errors');
            $customError .= '<br />' . implode('<br />', $errors);
        }
        
        return $isPassword;
    }
    
    /**
     * All valid chars for password
     *
     * @param bool $a_as_regex
     * @param bool $a_only_special_chars
     * @return string
     */
    public static function getPasswordValidChars(bool $a_as_regex = true, bool $a_only_special_chars = false) : ?string
    {
        if ($a_as_regex) {
            if ($a_only_special_chars) {
                return '/[_\.\+\?\#\-\*\@!\$\%\~\/\:\;]+/';
            } else {
                return '/^[A-Za-z0-9_\.\+\?\#\-\*\@!\$\%\~\/\:\;]+$/';
            }
        } else {
            return 'A-Z a-z 0-9 _.+?#-*@!$%~/:;';
        }
    }
    
    /**
     * @param string                 $clear_text_password The validated clear text password
     * @param array|ilObjUser|string $user                Could be an instance of ilObjUser, the users' loginname as string, or an array containing the users' loginname and id
     * @param string|null            $error_language_variable
     * @return bool
     */
    public static function isPasswordValidForUserContext(
        string $clear_text_password,
        $user,
        ?string &$error_language_variable = null
    ) : bool {
        $security = ilSecuritySettings::_getInstance();
        
        $login = null;
        
        if (is_string($user)) {
            $login = $user;
        } elseif (is_array($user)) {
            // Try to get loginname and user_id from array
            $login = $user['login'];
            $userId = $user['id'];
        } elseif ($user instanceof ilObjUser) {
            $login = $user->getLogin();
            $userId = $user->getId();
        }
        
        // The user context (user instance or id) can be used for further validation (e.g. compare a password with the users' password history, etc.) in future releases.
        
        if ($login && (int) $security->getPasswordMustNotContainLoginnameStatus() &&
            strpos(strtolower($clear_text_password), strtolower($login)) !== false
        ) {
            $error_language_variable = 'password_contains_parts_of_login_err';
            return false;
        }
        
        return true;
    }

    /**
     *    infotext for ilPasswordInputGUI setInfo()
     * @return string info about allowed chars for password
     * @static
     * @global <type> $lng
     */
    public static function getPasswordRequirementsInfo() : string
    {
        global $DIC;
        
        $lng = $DIC->language();
        
        $security = ilSecuritySettings::_getInstance();
        
        $infos = [sprintf($lng->txt('password_allow_chars'), self::getPasswordValidChars(false))];
        
        // check if password to short
        if ($security->getPasswordMinLength() > 0) {
            $infos[] = sprintf($lng->txt('password_to_short'), $security->getPasswordMinLength());
        }
        
        // check if password not to long
        if ($security->getPasswordMaxLength() > 0) {
            $infos[] = sprintf($lng->txt('password_to_long'), $security->getPasswordMaxLength());
        }
        
        // if password must contains Chars and Numbers
        if ($security->isPasswordCharsAndNumbersEnabled()) {
            $infos[] = $lng->txt('password_must_chars_and_numbers');
        }
        
        // if password must contains Special-Chars
        if ($security->isPasswordSpecialCharsEnabled()) {
            $infos[] = $lng->txt('password_must_special_chars');
        }
        
        if ($security->getPasswordNumberOfUppercaseChars() > 0) {
            $infos[] = sprintf(
                $lng->txt('password_must_contain_ucase_chars'),
                $security->getPasswordNumberOfUppercaseChars()
            );
        }
        
        if ($security->getPasswordNumberOfLowercaseChars() > 0) {
            $infos[] = sprintf(
                $lng->txt('password_must_contain_lcase_chars'),
                $security->getPasswordNumberOfLowercaseChars()
            );
        }
        
        return implode('<br />', $infos);
    }
    
    /**
     * Generate a number of passwords
     *
     * @static
     *
     */
    public static function generatePasswords(int $a_number) : array
    {
        $ret = [];
        srand((int) microtime() * 1000000);

        $security = ilSecuritySettings::_getInstance();
        
        for ($i = 1; $i <= $a_number; $i++) {
            $min = ($security->getPasswordMinLength() > 0)
                ? $security->getPasswordMinLength()
                : 6;
            $max = ($security->getPasswordMaxLength() > 0)
                ? $security->getPasswordMaxLength()
                : 10;
            if ($min > $max) {
                $max = $max + 1;
            }
            $random = new ilRandom();
            $length = $random->int($min, $max);
            $next = $random->int(1, 2);
            $vowels = "aeiou";
            $vowels_uc = strtoupper($vowels);
            $consonants = "bcdfghjklmnpqrstvwxyz";
            $consonants_uc = strtoupper($consonants);
            $numbers = "1234567890";
            $special = "_.+?#-*@!$%~";
            $pw = "";
            
            if ($security->getPasswordNumberOfUppercaseChars() > 0) {
                for ($j = 0; $j < $security->getPasswordNumberOfUppercaseChars(); $j++) {
                    switch ($next) {
                        case 1:
                            $pw .= $consonants_uc[$random->int(0, strlen($consonants_uc) - 1)];
                            $next = 2;
                            break;
                        
                        case 2:
                            $pw .= $vowels_uc[$random->int(0, strlen($vowels_uc) - 1)];
                            $next = 1;
                            break;
                    }
                }
            }
            
            if ($security->isPasswordCharsAndNumbersEnabled()) {
                $pw .= $numbers[$random->int(0, strlen($numbers) - 1)];
            }
            
            if ($security->isPasswordSpecialCharsEnabled()) {
                $pw .= $special[$random->int(0, strlen($special) - 1)];
            }
            
            $num_lcase_chars = max($security->getPasswordNumberOfLowercaseChars(), $length - strlen($pw));
            for ($j = 0; $j < $num_lcase_chars; $j++) {
                switch ($next) {
                    case 1:
                        $pw .= $consonants[$random->int(0, strlen($consonants) - 1)];
                        $next = 2;
                        break;
                    
                    case 2:
                        $pw .= $vowels[$random->int(0, strlen($vowels) - 1)];
                        $next = 1;
                        break;
                }
            }
            
            $pw = str_shuffle($pw);
            
            $ret[] = $pw;
        }
        return $ret;
    }
}
