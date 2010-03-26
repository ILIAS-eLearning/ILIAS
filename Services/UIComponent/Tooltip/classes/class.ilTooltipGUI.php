<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This is a utility class for the yui tooltips.
* this only works, if a parent has class="yui-skin-sam" attached.
*/
class ilTooltipGUI
{
	/**
	* Adds a tooltip to an HTML element
	*/
	static function addTooltip($a_el_id, $a_text)
	{
		global $tpl;
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initTooltip();
		$tpl->addOnLoadCode(
			'var ttip_'.$a_el_id.' = new YAHOO.widget.Tooltip("ttip_'.$a_el_id.
			'", { context:"'.$a_el_id.'", text:"'.htmlspecialchars($a_text).'" } );'); 
	}
	
}
?>