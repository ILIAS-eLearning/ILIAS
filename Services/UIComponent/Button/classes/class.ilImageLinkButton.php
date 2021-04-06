<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Image Link Button GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilImageLinkButton extends ilLinkButton
{
    protected $src; // [string]
    protected $force_title; // [bool]
    
    public static function getInstance()
    {
        return new self(self::TYPE_LINK);
    }
    
    
    //
    // properties
    //
    
    /**
     * Set image
     *
     * @param string $a_value
     * @param bool $a_is_internal
     */
    public function setImage($a_value, $a_is_internal = true)
    {
        if ((bool) $a_is_internal) {
            $a_value = ilUtil::getImagePath($a_value);
        }
        $this->src = trim($a_value);
    }
    
    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->src;
    }
    
    public function forceTitle($a_value)
    {
        $this->force_title = (bool) $a_value;
    }
    
    public function hasForceTitle()
    {
        return $this->force_title;
    }
    
    
    //
    // render
    //
    
    protected function prepareRender()
    {
        // get rid of parent "submit" css class...
    }
    
    protected function renderCaption()
    {
        $attr = array();
        $attr["src"] = $this->getImage();
        $attr["alt"] = $this->getCaption();
        if ($this->hasForceTitle()) {
            $attr["title"] = $this->getCaption();
        }
        return '<img' . $this->renderAttributesHelper($attr) . ' />';
    }
}
