<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAvatarBase
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilUserAvatarBase implements ilUserAvatar
{
    protected string $name = '';
    protected int $usrId = 0;

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function setUsrId(int $usrId) : void
    {
        $this->usrId = $usrId;
    }
}
