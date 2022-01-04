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
 * Lightbox handling
 * @author Alexander Killing <killing@leifos.de>
 *
 * @deprecated 10
 */
class ilLightboxGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected string $id = "";
    protected string $width;
    
    public function __construct(string $a_id)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->setId($a_id);
    }

    public function setId(string $a_val) : void
    {
        $this->id = $a_val;
    }

    public function getId() : string
    {
        return $this->id;
    }
    
    public function setWidth(string $a_val) : void
    {
        $this->width = $a_val;
    }

    public function getWidth() : string
    {
        return $this->width;
    }
    
    public static function getLocalLightboxJsPath() : string
    {
        return "./Services/UIComponent/Lightbox/js/Lightbox.js";
    }

    public function addLightbox(\ilGlobalTemplateInterface $a_tpl = null) : void
    {
        $tpl = $this->tpl;
        
        if ($a_tpl === null) {
            $a_tpl = $tpl;
        }

        $a_tpl->addJavaScript(self::getLocalLightboxJsPath());
        $a_tpl->addLightbox($this->getHTML(), $this->getId());
    }
    
    public function getHTML() : string
    {
        $tpl = new ilTemplate("tpl.lightbox.html", true, true, "Services/UIComponent/Lightbox");
        $tpl->setVariable("LIGHTBOX_CONTENT", "");
        $tpl->setVariable("ID", $this->getId());
        if ($this->getWidth() !== "") {
            $tpl->setVariable("WIDTH", "width: " . $this->getWidth() . ";");
        }
        return $tpl->get();
    }
}
