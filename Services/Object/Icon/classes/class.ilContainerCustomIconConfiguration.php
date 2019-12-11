<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfiguration.php';

/**
 * Class ilContainerCustomIconConfiguration
 */
class ilContainerCustomIconConfiguration extends \ilObjectCustomIconConfiguration
{
    /**
     * @return string
     */
    public function getBaseDirectory() : string
    {
        return 'container_data';
    }
}
