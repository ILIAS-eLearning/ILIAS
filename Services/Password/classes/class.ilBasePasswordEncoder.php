<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/interfaces/interface.ilPasswordEncoder.php';

/**
 * Class ilBasePasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
abstract class ilBasePasswordEncoder implements ilPasswordEncoder
{
	/**
	 * @var int Maximum password length
	 */
	const MAX_PASSWORD_LENGTH = 4096;

	/**
	 * Compares two passwords.
	 * This method implements a constant-time algorithm to compare passwords to
	 * avoid (remote) timing attacks.
	 * @url http://codahale.com/a-lesson-in-timing-attacks/
	 * @param string $known_string The first password
	 * @param string $user_string  The second password
	 * @return Boolean true if the two passwords are the same, false otherwise
	 */
	protected function comparePasswords($known_string, $user_string)
	{
		// Prevent issues if string length is 0
		$known_string .= chr(0);
		$user_string  .= chr(0);

		$known_string_length = strlen($known_string);
		$user_string_length  = strlen($user_string);

		// Set the result to the difference between the lengths
		$result = $known_string_length - $user_string_length;

		// Note that we ALWAYS iterate over the user-supplied length
		// This is to prevent leaking length information
		for($i = 0; $i < $user_string_length; $i++)
		{
			// Using % here is a trick to prevent notices
			// It's safe, since if the lengths are different
			// $result is already non-0
			$result |= (ord($known_string[$i % $known_string_length]) ^ ord($user_string[$i]));
		}

		// They are only identical strings if $result is exactly 0...
		return 0 === $result;
	}

	/**
	 * Checks if the password is too long.
	 * @param string $password The password
	 * @return bool true if the password is too long, false otherwise
	 */
	protected function isPasswordTooLong($password)
	{
		return strlen($password) > self::MAX_PASSWORD_LENGTH;
	}
}
