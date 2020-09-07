<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilExportConfig.php");
/**
 * Export configuration for pages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilCOPageExportConfig extends ilExportConfig
{
    protected $master_only = false;
    protected $include_media = true;

    /**
     * Set master language only
     *
     * @param bool $a_val export only master language
     */
    public function setMasterLanguageOnly($a_val, $a_include_media = true)
    {
        $this->master_only = $a_val;
        $this->include_media = $a_include_media;
    }

    /**
     * Get master language only
     *
     * @return bool export only master language
     */
    public function getMasterLanguageOnly()
    {
        return $this->master_only;
    }

    /**
     * Get include media
     *
     * @return bool export media?
     */
    public function getIncludeMedia()
    {
        return $this->include_media;
    }
}
