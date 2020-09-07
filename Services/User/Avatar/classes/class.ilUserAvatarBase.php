<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAvatarBase
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilUserAvatarBase implements ilUserAvatar
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $usrId = 0;

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;
    }
}
