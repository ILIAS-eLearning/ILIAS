<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBasePasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
abstract class ilBasePasswordEncoder implements ilPasswordEncoder
{
    /** @var int Maximum password length */
    const MAX_PASSWORD_LENGTH = 4096;

    /**
     * Compares two passwords.
     * This method implements a constant-time algorithm to compare passwords to
     * avoid (remote) timing attacks.
     * @url http://codahale.com/a-lesson-in-timing-attacks/
     * @param string $knownString The first password
     * @param string $userString  The second password
     * @return Boolean true if the two passwords are the same, false otherwise
     */
    protected function comparePasswords(string $knownString, string $userString) : bool
    {
        $knownString .= chr(0);
        $userString  .= chr(0);

        $known_string_length = strlen($knownString);
        $user_string_length  = strlen($userString);

        $result = $known_string_length - $user_string_length;

        for ($i = 0; $i < $user_string_length; $i++) {
            $result |= (ord($knownString[$i % $known_string_length]) ^ ord($userString[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return 0 === $result;
    }

    /**
     * Checks if the password is too long.
     * @param string $password The password
     * @return bool true if the password is too long, false otherwise
     */
    protected function isPasswordTooLong(string $password) : bool
    {
        return strlen($password) > self::MAX_PASSWORD_LENGTH;
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedByRuntime() : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSalt() : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresReencoding(string $encoded) : bool
    {
        return false;
    }
}
