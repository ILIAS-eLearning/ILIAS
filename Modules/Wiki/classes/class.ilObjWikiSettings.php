<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Wiki settings application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjWikiSettings extends ilObject2
{
    /**
     * Get type
     *
     * @param
     * @return
     */
    public function initType()
    {
        $this->type = "wiks";
    }
}
