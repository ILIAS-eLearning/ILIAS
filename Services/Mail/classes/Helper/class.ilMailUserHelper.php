<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailUserHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailUserHelper
{
    /**
     * @param array $usrIds
     * @return string[]
     */
    public function getUsernameMapForIds(array $usrIds) : array
    {
        return ilUserUtil::getNamePresentation($usrIds, false, false, '', false, true, false);
    }
}