<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

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
