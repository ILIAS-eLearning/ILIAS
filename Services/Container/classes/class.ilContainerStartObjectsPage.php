<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Container start objects page object
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilContainerStartObjectsPage extends ilPageObject
{
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return "cstr";
    }
}
