<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This is a utility class for the yui overlays.
* this only works, if a parent has class="yui-skin-sam" attached.
*/
class ilOverlayGUI
{
	protected $width = "";
	protected $height = "";
	protected $fixed_center = false;
	protected $visible = false;
	protected $anchor_el_id = "";
	protected $anchor_ov_corner = "";
	protected $anchor_anch_corner = "";
	protected $auto_hide = false;
	protected $close_el = null;
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_overlay_el_id)
	{
		$this->overlay_el_id = $a_overlay_el_id;
	}

	/**
	 * Set anchor element
	 *
	 * @param	string		anchor element id
	 * @param	string		overlay corner ("tl", "tr", "bl", "br") aligned to...
	 * @param	string		anchor corner ("tl", "tr", "bl", "br")
	 */
	function setAnchor($a_anchor_el_id, $a_ov_corner = "tl", $a_anch_corner = "bl")
	{
		$this->anchor_el_id = $a_anchor_el_id;
		$this->anchor_ov_corner = $a_ov_corner;
		$this->anchor_anch_corner = $a_anch_corner;
	}

	/**
	 * Set size
	 *
	 * @param	string		width, e.g. 300px
	 * @param	string		height, e.g. 300px
	 */
	function setSize($a_width = "", $a_height = "")
	{
		$this->width = $a_width;
		$this->height = $a_height;
	}
	
	/**
	 * Set fixed center
	 *
	 * @param	boolean		fixed center
	 */
	function setFixedCenter($a_fixed_center = true)
	{
		$this->fixed_center = $a_fixed_center;
	}

	/**
	 * Set visible
	 *
	 * @param	boolean		visible
	 */
	function setVisible($a_visible = true)
	{
		$this->visible = $a_visible;	
	}
	
	/**
	 * Set trigger element
	 *
	 * @param	string		element id
	 * @param	string		event ("click" or "mouseover")
	 */
	function setTrigger($a_el_id, $a_event = "click", $a_trigger_anchor_el_id = null)
	{
		$this->trigger_el_id = $a_el_id;
		$this->trigger_event = $a_event;
		$this->trigger_anchor_el_id = $a_trigger_anchor_el_id;
	}
	
	/**
	 * Set auto hiding
	 *
	 * @param	boolean	auto hide
	 */
	function setAutoHide($a_val)
	{
		$this->auto_hide = $a_val;
	}
	
	/**
	 * Get auto_hide
	 *
	 * @return	boolean	auto hide
	 */
	function getAutoHide()
	{
		return $this->auto_hide;
	}
	
	/**
	 * Set close element id
	 *
	 * @param	string	close element id
	 */
	function setCloseElementId($a_val)
	{
		$this->close_el = $a_val;
	}
	
	/**
	 * Get close element id
	 *
	 * @return	string	clos element id
	 */
	function getCloseElementId()
	{
		return $this->close_el;
	}
	
	/**
	 * Makes an existing HTML element an overlay 
	 */
	function getOnLoadCode()
	{
		// yui cfg string
		$yuicfg["visible"] = $this->visible ? true : false;
		
		if ($this->width != "")
		{
			$yuicfg["width"] = $this->width;
		}
		
		if ($this->height != "")
		{
			$yuicfg["height"] = $this->height;
		}
		$yuicfg["fixedcenter"] = $this->fixed_center ? true : false;
		if ($this->anchor_el_id != "")
		{
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

		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
//var_dump(ilJsonUtil::encode($cfg));
		return 'il.Overlay.add("'.$this->overlay_el_id.'", '.
			ilJsonUtil::encode($cfg).'); ';
	}
	
	/**
	 * Makes an existing HTML element an overlay
	 */
	function add()
	{
		global $tpl;
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		
		self::initJavascript();
		$tpl->addOnLoadCode($this->getOnLoadCode()); 
	}
	
	/**
	 * Init javascript
	 *
	 * @param
	 * @return
	 */
	static function initJavascript()
	{
		global $tpl;
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initOverlay();
		$tpl->addJavascript("./Services/UIComponent/Overlay/js/ilOverlay.js");
	}
	
	
	/**
	 * Get trigger onload code
	 *
	 * @param
	 * @return
	 */
	function getTriggerOnLoadCode($a_tr_id, $a_tr_event, $a_anchor_el_id, $a_center = false,
								  $a_ov_corner = "tl", $a_anch_corner = "bl")
	{
		$center = ($a_center) ? "true" : "false";
		return 'il.Overlay.addTrigger("'.$a_tr_id.'","'.$a_tr_event.'","'.$this->overlay_el_id.'","'.
			$a_anchor_el_id.'", '.$center.',"'.$a_ov_corner.'","'.$a_anch_corner.'"); ';
	}
	
	/**
	 * Add trigger
	 */
	function addTrigger($a_tr_id, $a_tr_event, $a_anchor_el_id, $a_center = false,
						$a_ov_corner = "tl", $a_anch_corner = "bl")
	{
		global $tpl;
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
//echo "-".$a_tr_id."-".$a_tr_event."-".$a_anchor_el_id."-";
		self::initJavascript();
		$tpl->addOnLoadCode($this->getTriggerOnLoadCode($a_tr_id, $a_tr_event, $a_anchor_el_id, $a_center,
				$a_ov_corner, $a_anch_corner)); 
	}
	
	
	
}
?>