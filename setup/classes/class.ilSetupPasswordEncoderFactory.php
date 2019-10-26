<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSetupPasswordManager
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSetupPasswordEncoderFactory extends ilUserPasswordEncoderFactory
{
    /**
     * @inheritdoc
     */
    protected function getValidEncoders($config) : array
    {
        return [
            new ilArgon2idPasswordEncoder($config),
            new ilBcryptPhpPasswordEncoder($config),
            new ilMd5PasswordEncoder($config),
        ];
    }
}
