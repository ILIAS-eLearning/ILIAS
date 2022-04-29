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
 * Class ilBasePasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
abstract class ilBasePasswordEncoder implements ilPasswordEncoder
{
    /** @var int Maximum password length */
    private const MAX_PASSWORD_LENGTH = 4096;

    /**
     * Compares two passwords.
     * This method implements a constant-time algorithm to compare passwords to
     * avoid (remote) timing attacks.
     * @url http://codahale.com/a-lesson-in-timing-attacks/
     * @param string $knownString The first password
     * @param string $userString  The second password
     * @return bool true if the two passwords are the same, false otherwise
     */
    protected function comparePasswords(string $knownString, string $userString) : bool
    {
        $knownString .= chr(0);
        $userString .= chr(0);

        $known_string_length = strlen($knownString);
        $user_string_length = strlen($userString);

        $result = $known_string_length - $user_string_length;

        for ($i = 0; $i < $user_string_length; ++$i) {
            $result |= (ord($knownString[$i % $known_string_length]) ^ ord($userString[$i]));
        }

        return 0 === $result;
    }

    protected function isPasswordTooLong(string $password) : bool
    {
        return strlen($password) > self::MAX_PASSWORD_LENGTH;
    }

    public function isSupportedByRuntime() : bool
    {
        return true;
    }

    public function requiresSalt() : bool
    {
        return false;
    }

    public function requiresReencoding(string $encoded) : bool
    {
        return false;
    }
}
