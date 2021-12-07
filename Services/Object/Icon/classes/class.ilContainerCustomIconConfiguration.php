<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

class ilContainerCustomIconConfiguration extends ilObjectCustomIconConfiguration
{
    public function getBaseDirectory() : string
    {
        return 'container_data';
    }
}
