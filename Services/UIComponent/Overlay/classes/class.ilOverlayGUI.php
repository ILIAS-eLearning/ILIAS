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
 * This is a utility class for the yui overlays.
 * this only works, if a parent has class="yui-skin-sam" attached.
 *
 * @deprecated 10
 */
class ilOverlayGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected string $width = "";
    protected string $height = "";
    protected bool $fixed_center = false;
    protected bool $visible = false;
    protected string $anchor_el_id = "";
    protected string $anchor_ov_corner = "";
    protected string $anchor_anch_corner = "";
    protected bool $auto_hide = false;
    protected ?string $close_el = null;
    protected string $trigger_el_id = '';
    protected string $trigger_event = '';
    protected ?string $trigger_anchor_el_id = null;
    protected string $overlay_el_id = '';

    public function __construct(string $a_overlay_el_id)
    {
        global $DIC;

        // please check learning modules (e.g. rating) before removing this globals
        // use (they do not use standard template)
        $this->tpl = $GLOBALS["tpl"];
        $this->overlay_el_id = $a_overlay_el_id;
    }

    /**
     * @param string $a_anchor_el_id anchor element id
     * @param string $a_ov_corner overlay corner ("tl", "tr", "bl", "br") aligned to...
     * @param string $a_anch_corner anchor corner ("tl", "tr", "bl", "br")
     */
    public function setAnchor(
        string $a_anchor_el_id,
        string $a_ov_corner = "tl",
        string $a_anch_corner = "bl"
    ) : void {
        $this->anchor_el_id = $a_anchor_el_id;
        $this->anchor_ov_corner = $a_ov_corner;
        $this->anchor_anch_corner = $a_anch_corner;
    }

    public function setSize(
        string $a_width = "",
        string $a_height = ""
    ) : void {
        $this->width = $a_width;
        $this->height = $a_height;
    }

    public function setFixedCenter(bool $a_fixed_center = true) : void
    {
        $this->fixed_center = $a_fixed_center;
    }

    public function setVisible(bool $a_visible = true) : void
    {
        $this->visible = $a_visible;
    }

    public function setTrigger(
        string $a_el_id,
        string $a_event = "click",
        ?string $a_trigger_anchor_el_id = null
    ) : void {
        $this->trigger_el_id = $a_el_id;
        $this->trigger_event = $a_event;
        $this->trigger_anchor_el_id = $a_trigger_anchor_el_id;
    }

    public function setAutoHide(bool $a_val) : void
    {
        $this->auto_hide = $a_val;
    }

    public function getAutoHide() : bool
    {
        return $this->auto_hide;
    }

    public function setCloseElementId(string $a_val) : void
    {
        $this->close_el = $a_val;
    }

    public function getCloseElementId() : string
    {
        return $this->close_el;
    }

    public function getOnLoadCode() : string
    {
        // yui cfg string
        $yuicfg["visible"] = $this->visible;
        
        if ($this->width !== "") {
            $yuicfg["width"] = $this->width;
        }
        
        if ($this->height !== "") {
            $yuicfg["height"] = $this->height;
        }
        $yuicfg["fixedcenter"] = $this->fixed_center;
        if ($this->anchor_el_id !== "") {
            $yuicfg["context"] = array($this->anchor_el_id, $this->anchor_ov_corner,
                    $this->anchor_anch_corner, array("beforeShow", "windowResize"));
        }
        // general cfg string
        $cfg["yuicfg"] = $yuicfg;
        $cfg["trigger"] = $this->trigger_el_id;
        $cfg["trigger_event"] = $this->trigger_event;
        $cfg["anchor_id"] = $this->trigger_anchor_el_id;
        $cfg["auto_hide"] = $this->auto_hide;
        $cfg["close_el"] = $this->close_el;

        //var_dump(json_encode($cfg, JSON_THROW_ON_ERROR));
        return 'il.Overlay.add("' . $this->overlay_el_id . '", ' .
            json_encode($cfg, JSON_THROW_ON_ERROR) . '); ';
    }

    public function add() : void
    {
        $tpl = $this->tpl;
        self::initJavascript();
        $tpl->addOnLoadCode($this->getOnLoadCode());
    }

    public static function initJavascript() : void
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        
        ilYuiUtil::initOverlay($tpl);
        $tpl->addJavaScript("./Services/UIComponent/Overlay/js/ilOverlay.js");
    }
    
    public function getTriggerOnLoadCode(
        string $a_tr_id,
        string $a_tr_event,
        string $a_anchor_el_id,
        bool $a_center = false,
        string $a_ov_corner = "tl",
        string $a_anch_corner = "bl"
    ) : string {
        $center = ($a_center) ? "true" : "false";
        return 'il.Overlay.addTrigger("' . $a_tr_id . '","' . $a_tr_event . '","' . $this->overlay_el_id . '","' .
            $a_anchor_el_id . '", ' . $center . ',"' . $a_ov_corner . '","' . $a_anch_corner . '"); ';
    }
    
    public function addTrigger(
        string $a_tr_id,
        string $a_tr_event,
        string $a_anchor_el_id,
        bool $a_center = false,
        string $a_ov_corner = "tl",
        string $a_anch_corner = "bl"
    ) : void {
        $tpl = $this->tpl;

        self::initJavascript();
        $tpl->addOnLoadCode($this->getTriggerOnLoadCode(
            $a_tr_id,
            $a_tr_event,
            $a_anchor_el_id,
            $a_center,
            $a_ov_corner,
            $a_anch_corner
        ));
    }
}
