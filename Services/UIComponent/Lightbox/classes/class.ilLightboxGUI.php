<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Lighbox handling
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUIComponentLightbox
 */
class ilLightboxGUI
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $id = "";
    
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_id)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->setId($a_id);
    }
    
    /**
     * Set Id
     *
     * @param string $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
     * Get Id
     *
     * @return string id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set width
     *
     * @param string $a_val width
     */
    public function setWidth($a_val)
    {
        $this->width = $a_val;
    }
    
    /**
     * Get width
     *
     * @return string width
     */
    public function getWidth()
    {
        return $this->width;
    }
    
    /**
     * Get local path of jQuery file
     */
    public static function getLocalLightboxJsPath()
    {
        return "./Services/UIComponent/Lightbox/js/Lightbox.js";
    }

    /**
     * Init lightbox
     */
    public function addLightbox($a_tpl = null)
    {
        $tpl = $this->tpl;
        
        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }

        $a_tpl->addJavaScript(self::getLocalLightboxJsPath());
        $a_tpl->addLightbox($this->getHTML(), $this->getId());
    }
    
    /**
     * Get HTML
     *
     * @param
     * @return
     */
    public function getHTML()
    {
        $tpl = new ilTemplate("tpl.lightbox.html", true, true, "Services/UIComponent/Lightbox");
        $tpl->setVariable("LIGHTBOX_CONTENT", "");
        $tpl->setVariable("ID", $this->getId());
        if ($this->getWidth() != "") {
            $tpl->setVariable("WIDTH", "width: " . $this->getWidth() . ";");
        }
        return $tpl->get();
    }
}
