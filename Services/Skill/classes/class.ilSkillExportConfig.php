<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilExportConfig.php");
/**
 * Export configuration for skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesSkill
 */
class ilSkillExportConfig extends ilExportConfig
{
    const MODE_SKILLS = "";
    const MODE_PROFILES = "prof";
    protected $selected_nodes = false;
    protected $selected_profiles = false;
    protected $mode = "";

    /**
     * Set mode
     *
     * @param string $a_val mode
     */
    public function setMode($a_val)
    {
        $this->mode = $a_val;
    }
    
    /**
     * Get mode
     *
     * @return string mode
     */
    public function getMode()
    {
        return $this->mode;
    }
    
    /**
     * Set export selected nodes
     *
     * @param array $a_val array of int
     */
    public function setSelectedNodes($a_val)
    {
        $this->selected_nodes = $a_val;
    }

    /**
     * Get export selected nodes
     *
     * @return array array of int
     */
    public function getSelectedNodes()
    {
        return $this->selected_nodes;
    }

    /**
     * Set selected profiles
     *
     * @param array $a_val array of int (profile ids)
     */
    public function setSelectedProfiles($a_val)
    {
        $this->selected_profiles = $a_val;
    }

    /**
     * Get selected profiles
     *
     * @return array array of int (profile ids)
     */
    public function getSelectedProfiles()
    {
        return $this->selected_profiles;
    }
}
