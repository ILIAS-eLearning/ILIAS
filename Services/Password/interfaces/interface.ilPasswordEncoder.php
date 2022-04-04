<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilPasswordEncoder
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
interface ilPasswordEncoder
{
    /**
     * Encodes the raw password.
     * @param string $raw  The password to encode
     * @param string $salt The salt
     * @return string The encoded password
     */
    public function encodePassword(string $raw, string $salt) : string;

    /**
     * Checks a raw password against an encoded password. The raw password has to be injected into the encoder instance before.
     * @param string $encoded An encoded password
     * @param string $raw     A raw password
     * @param string $salt    The salt, may be empty
     * @return Boolean true if the password is valid, false otherwise
     */
    public function isPasswordValid(string $encoded, string $raw, string $salt) : bool;

    /**
     * Returns a unique name/id of the concrete password encoder
     */
    public function getName() : string;

    /**
     * Returns whether the encoder requires a salt
     */
    public function requiresSalt() : bool;

    /**
     * Returns whether the encoded password needs to be re-encoded
     */
    public function requiresReencoding(string $encoded) : bool;

    /**
     * Returns whether the encoder is supported by the runtime (PHP, HHVM, ...)
     */
    public function isSupportedByRuntime() : bool;
}
