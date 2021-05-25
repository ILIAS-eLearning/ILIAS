<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * manifest.xml file not found-exception for import
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilManifestFileNotFoundImportException extends ilImportException
{
    private $manifest_dir = "";
    private $tmp_dir = "";
    
    /**
     * Set manifest directory
     *
     * @param string $a_val manifest directory
     */
    public function setManifestDir($a_val)
    {
        $this->manifest_dir = $a_val;
    }
    
    /**
     * Get manifest directory
     *
     * @return string manifest directory
     */
    public function getManifestDir()
    {
        return $this->manifest_dir;
    }
    
    /**
     * Set temporary directory
     *
     * @param string $a_val temporary directory
     */
    public function setTmpDir($a_val)
    {
        $this->tmp_dir = $a_val;
    }
    
    /**
     * Get temporary directory
     *
     * @return string temporary directory
     */
    public function getTmpDir()
    {
        return $this->tmp_dir;
    }
}
