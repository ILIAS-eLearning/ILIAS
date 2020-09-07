<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserPasswordEncoderFactory.php';

/**
 * Class ilSetupPasswordManager
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSetupPasswordEncoderFactory extends \ilUserPasswordEncoderFactory
{
    /**
     * @inheritdoc
     */
    protected function getValidEncoders($config) : array
    {
        return [
            new ilBcryptPhpPasswordEncoder($config),
            new ilMd5PasswordEncoder($config),
        ];
    }
}
