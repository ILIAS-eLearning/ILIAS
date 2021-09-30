<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailEnvironmentHelper
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailEnvironmentHelper
{
    public function getHttpPath() : string
    {
        return ilUtil::_getHttpPath();
    }

    public function getClientId() : string
    {
        $clientId = '';
        if (defined('CLIENT_NAME')) {
            $clientId = CLIENT_NAME;
        }
        
        return $clientId;
    }
}
