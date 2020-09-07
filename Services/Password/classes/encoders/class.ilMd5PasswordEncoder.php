<?php
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
    public function __construct(array $config = array())
    {
    }

    /**
     * {@inheritdoc}
     * @throws ilPasswordException
     */
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            require_once 'Services/Password/exceptions/class.ilPasswordException.php';
            throw new ilPasswordException('Invalid password.');
        }

        return md5($raw);
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return !$this->isPasswordTooLong($raw) && $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'md5';
    }
}
