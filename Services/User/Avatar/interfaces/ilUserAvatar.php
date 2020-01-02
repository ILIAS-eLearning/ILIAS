<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLetterAvatar
 * @author Alexander Killing <killing@leifos.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilUserAvatar
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param int $usrId
     */
    public function setUsrId($usrId);

    /**
     * @param string $name
     */
    public function setName($name);
}
