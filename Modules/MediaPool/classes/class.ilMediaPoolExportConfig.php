<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Export configuration for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaPoolExportConfig extends ilExportConfig
{
    protected $master_only = false;
    protected $include_media = true;

    /**
     * Set master language only
     *
     * @param bool $a_val
     * @param bool $a_include_media
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
