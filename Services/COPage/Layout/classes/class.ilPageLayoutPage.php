<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Page layout page object
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageLayoutPage extends ilPageObject
{
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return "stys";
    }
}
