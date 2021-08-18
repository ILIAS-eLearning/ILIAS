<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Container page object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilContainerPage extends ilPageObject
{
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return "cont";
    }
}
