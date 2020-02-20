<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilImportConfig.php");
/**
 * Import configuration for pages
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilCOPageImportConfig extends ilImportConfig
{
    protected $update_if_exists = false;
    protected $force_lang = "";
    protected $reuse_media = false;
    protected $skip_int_link_resolve = false;

    /**
     * Set update if exists
     *
     * @param bool $a_val update page if it already exists
     */
    public function setUpdateIfExists($a_val)
    {
        $this->update_if_exists = $a_val;
    }
    
    /**
     * Get update if exists
     *
     * @return bool update page if it already exists
     */
    public function getUpdateIfExists()
    {
        return $this->update_if_exists;
    }
    
    /**
     * Set force language
     *
     * @param string $a_val language
     */
    public function setForceLanguage($a_val)
    {
        $this->force_lang = $a_val;
    }
    
    /**
     * Get force language
     *
     * @return string language
     */
    public function getForceLanguage()
    {
        return $this->force_lang;
    }

    //setReuseOriginallyExportedMedia

    /**
     * Set reuse originally exported media
     *
     * @param bool $a_val reuse originally exported media
     */
    public function setReuseOriginallyExportedMedia($a_val)
    {
        $this->reuse_media = $a_val;
    }

    /**
     * Get reuse originally exported media
     *
     * @return bool reuse originally exported media
     */
    public function getReuseOriginallyExportedMedia()
    {
        return $this->reuse_media;
    }

    /**
     * Set skip internal link resolve
     *
     * @param bool $a_val do not resolve internal links (as it is done at another place)
     */
    public function setSkipInternalLinkResolve($a_val)
    {
        $this->skip_int_link_resolve = $a_val;
    }
    
    /**
     * Get skip internal link resolve
     *
     * @return bool do not resolve internal links (as it is done at another place)
     */
    public function getSkipInternalLinkResolve()
    {
        return $this->skip_int_link_resolve;
    }
}
