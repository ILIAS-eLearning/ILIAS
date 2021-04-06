<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Import configuration for media objects
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaObjectsImportConfig extends ilImportConfig
{
    protected $use_previous_import_ids = false;

    /**
     * Set use previous import ids
     *
     * @param bool $a_val use previous import ids
     */
    public function setUsePreviousImportIds($a_val)
    {
        $this->use_previous_import_ids = $a_val;
    }

    /**
     * Get use previous import ids
     *
     * @return bool use previous import ids
     */
    public function getUsePreviousImportIds()
    {
        return $this->use_previous_import_ids;
    }
}
