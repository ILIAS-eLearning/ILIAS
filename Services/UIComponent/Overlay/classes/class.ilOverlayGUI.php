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
	 * @param	string		event ("onclicke" or "onmouseover")
	 */
	function setTrigger($a_el_id, $a_event = "onclick")
	{
		$this->trigger_el_id = $a_el_id;
		$this->trigger_event = $a_event;
	}
	
	/**
	 * Makes an existing HTML element an overlay 
	 */
	function add()
	{
		global $tpl;
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		
		$cfg_str = $lim = "";
		
		$cfg_str.= $lim.'visible:'.($this->visible ? 'true' : 'false');
		$lim = ",";
		
		if ($this->width != "")
		{
			$cfg_str.= $lim.'width:"'.$this->width.'"';
			$lim = ",";
		}
		if ($this->height != "")
		{
			$cfg_str.= $lim.'height:"'.$this->height.'"';
			$lim = ",";
		}
		if ($this->fixed_center)
		{
			$cfg_str.= $lim.'fixedcenter:true';
			$lim = ",";
		}
		if ($this->anchor_el_id != "")
		{
			$cfg_str.= $lim.'context: ["'.$this->anchor_el_id.'","'.
				$this->anchor_ov_corner.'","'.$this->anchor_anch_corner.'"'.
				', ["beforeShow", "windowResize"]]';
			$lim = ",";
		}

		ilYuiUtil::initOverlay();
		$tpl->addJavascript("./Services/UIComponent/Overlay/js/ilOverlay.js");
		$tpl->addOnLoadCode(
			'ilOverlay.add("'.$this->overlay_el_id.'", new YAHOO.widget.Overlay("'.$this->overlay_el_id.'",'.
			'{'.$cfg_str.'} ), "'.$this->trigger_el_id.'","'.$this->trigger_event.'"); '); 
	}
	
}
?>