<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This is a utility class for the yui tooltips.
* this only works, if a parent has class="yui-skin-sam" attached.
*/
class ilTooltipGUI
{
	static protected $initialized = false;
	static protected $library_initialized = false;
	
	/**
	 * Adds a tooltip to an HTML element
	 *
	 * @param string $a_el_id element id
	 * @param string $a_el_id tooltip text
	 * @param string $a_el_id element id of container the tooltip should be added to
	 */
	static function addTooltip($a_el_id, $a_text, $a_container = "",
		$a_my = "bottom center", $a_at = "top center", $a_use_htmlspecialchars = true)
	{
		global $tpl;
		
		self::initLibrary();		
		if (!self::$initialized)
		{
			$tpl->addJavascript("./Services/UIComponent/Tooltip/js/ilTooltip.js");
			$tpl->addOnLoadCode('il.Tooltip.init();', 3);
			self::$initialized = true;
		}
		
		$code = self::getTooltip($a_el_id, $a_text, $a_container, $a_my, $a_at,
			$a_use_htmlspecialchars);
		$tpl->addOnLoadCode($code); 
	}
	
	/**
	 * Get tooltip js code
	 *
	 * @param string $a_el_id element id
	 * @param string $a_el_id tooltip text
	 * @param string $a_el_id element id of container the tooltip should be added to
	 */
	static function getToolTip($a_el_id, $a_text, $a_container = "",
		$a_my = "bottom center", $a_at = "top center", $a_use_htmlspecialchars = true)
	{
		$addstr = "";

		// not needed, just make sure the position plugin is included
//		$addstr.= ", position: {viewport: $('#fixed_content')}";
		
		if ($a_container != "")
		{
			$addstr.= ", container: '".$a_container."'";
		}

		if ($a_use_htmlspecialchars)
		{
			$a_text = htmlspecialchars(str_replace(array("\n", "\r"), "", $a_text));
		}
		else
		{
			$a_text = str_replace(array("\n", "\r", "'", '"'), array("", "", "\'", '\"'), $a_text);
		}
		return 'il.Tooltip.add("'.$a_el_id.'", {'.
			' context:"'.$a_el_id.'",'.
			' my:"'.$a_my.'",'.
			' at:"'.$a_at.'",'.
			' text:"'.$a_text.'" '.$addstr.'} );';
	}
	
	/**
	 * Initializes the needed tooltip libraries.
	 */
	static function initLibrary()
	{
		global $tpl;
		
		if (self::$library_initialized)
			return;

		//			include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		//			ilYuiUtil::initTooltip();
		$tpl->addCss("./Services/UIComponent/Tooltip/lib/qtip_2_2_0/jquery.qtip.min.css");
		$tpl->addJavascript("./Services/UIComponent/Tooltip/lib/qtip_2_2_0/jquery.qtip.min.js");
		
		self::$library_initialized = true;
	}
}
?>