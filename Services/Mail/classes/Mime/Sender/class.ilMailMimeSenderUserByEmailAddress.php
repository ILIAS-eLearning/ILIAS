<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeSenderUserByEmailAddress
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeSenderUserByEmailAddress extends ilMailMimeSenderUser
{
    public function __construct(ilSetting $settings, string $emailAddress)
    {
        $user = new ilObjUser();
        $user->setEmail($emailAddress);

        parent::__construct($settings, $user);
    }
}
