<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Import configuration for pages
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCOPageImportConfig extends ilImportConfig
{
    protected bool $update_if_exists = false;
    protected string $force_lang = "";
    protected bool $reuse_media = false;
    protected bool $skip_int_link_resolve = false;

    /**
     * Set update if exists
     * @param bool $a_val update page if it already exists
     */
    public function setUpdateIfExists(bool $a_val) : void
    {
        $this->update_if_exists = $a_val;
    }

    public function getUpdateIfExists() : bool
    {
        return $this->update_if_exists;
    }
    
    public function setForceLanguage(string $a_val) : void
    {
        $this->force_lang = $a_val;
    }
    
    public function getForceLanguage() : string
    {
        return $this->force_lang;
    }

    /**
     * Set reuse originally exported media
     * @param bool $a_val reuse originally exported media
     */
    public function setReuseOriginallyExportedMedia(bool $a_val) : void
    {
        $this->reuse_media = $a_val;
    }

    public function getReuseOriginallyExportedMedia() : bool
    {
        return $this->reuse_media;
    }

    /**
     * Set skip internal link resolve
     * @param bool $a_val do not resolve internal links (as it is done at another place)
     */
    public function setSkipInternalLinkResolve(bool $a_val) : void
    {
        $this->skip_int_link_resolve = $a_val;
    }
    
    public function getSkipInternalLinkResolve() : bool
    {
        return $this->skip_int_link_resolve;
    }
}
