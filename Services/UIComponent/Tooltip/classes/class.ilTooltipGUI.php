<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This is a utility class for the yui tooltips.
* this only works, if a parent has class="yui-skin-sam" attached.
*/
class ilTooltipGUI
{
	static protected $initialized = false;
	
	/**
	 * Adds a tooltip to an HTML element
	 *
	 * @param string $a_el_id element id
	 * @param string $a_el_id tooltip text
	 * @param string $a_el_id element id of container the tooltip should be added to
	 */
	static function addTooltip($a_el_id, $a_text, $a_container = "")
	{
		global $tpl;
		
		if (!self::$initialized)
		{
			include_once("./Services/YUI/classes/class.ilYuiUtil.php");
			ilYuiUtil::initTooltip();
			$tpl->addJavascript("./Services/UIComponent/Tooltip/js/ilTooltip.js");
			$tpl->addOnLoadCode('ilTooltip.init();', 3);
			self::$initialized = true;
		}
		
		$addstr = "";
		if ($a_container != "")
		{
			$addstr.= ", container: '".$a_container."'";
		}
		$tpl->addOnLoadCode('ilTooltip.add("'.$a_el_id.
			'", { context:"'.$a_el_id.'", text:"'.htmlspecialchars($a_text).'" '.$addstr.'} );'); 
	}
	
}
?>