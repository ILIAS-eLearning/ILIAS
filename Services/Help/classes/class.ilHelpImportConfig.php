<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import configuration for help modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilHelpImportConfig extends ilImportConfig
{
    protected $module_id = 0;

    /**
     * Set module id
     *
     * @param int $a_val module id
     */
    public function setModuleId($a_val)
    {
        $this->module_id = $a_val;
    }

    /**
     * Get module id
     *
     * @return int module id
     */
    public function getModuleId()
    {
        return $this->module_id;
    }
}
