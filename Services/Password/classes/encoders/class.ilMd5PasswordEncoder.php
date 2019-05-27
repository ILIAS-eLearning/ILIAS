<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/class.ilBasePasswordEncoder.php';

/**
 * Class ilMd5PasswordEncoder
 * This class implements the ILIAS password encryption mechanism used in ILIAS3/ILIAS4
 * We didn't use any salts until we introduced this password service
 * To implement a new generic Message Digest encoder, please create a separate class.
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilMd5PasswordEncoder extends ilBasePasswordEncoder
{
    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
    }

    /**
     * @inheritDoc
     * @throws ilPasswordException
     */
    public function encodePassword(string $raw, string $salt) : string
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new ilPasswordException('Invalid password.');
        }

        return md5($raw);
    }

    /**
     * @inheritDoc
     * @throws ilPasswordException
     */
    public function isPasswordValid(string $encoded, string $raw, string $salt) : bool
    {
        return !$this->isPasswordTooLong($raw) && $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }

    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return 'md5';
    }
}
