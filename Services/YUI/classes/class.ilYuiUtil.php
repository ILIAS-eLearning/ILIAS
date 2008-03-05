<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Yahoo YUI Library Utility functions
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilYuiUtil
{
	/**
	* Init YUI Connection module
	*/
	static function initConnection()
	{
		global $tpl;
		
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/yahoo/yahoo-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/event/event-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/connection/connection-min.js");
	}
	
	/**
	* Init YUI Drag and Drop
	*/
	static function initDragDrop()
	{
		global $tpl;

		$tpl->addJavaScript("./Services/YUI/js/2_5_0/yahoo-dom-event/yahoo-dom-event.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/dragdrop/dragdrop-min.js");
	}


	/**
	* Init YUI Menu module
	*/
	static function initMenu()
	{
		global $tpl;
		
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/yahoo/yahoo-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/event/event.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/dom/dom.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/container/container_core.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/menu/menu.js");
		$tpl->addCss("./Services/YUI/js/2_5_0/menu/assets/menu.css");
	}

	/**
	* Init YUI Overlay module
	*/
	static function initOverlay()
	{
		global $tpl;
		
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/yahoo/yahoo-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/event/event.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/dom/dom.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/container/container_core.js");
	}
	
	/**
	* Init YUI Simple Dialog
	*/
	static function initSimpleDialog()
	{
		global $tpl;

		$tpl->addJavaScript("./Services/YUI/js/2_5_0/yahoo/yahoo-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/event/event.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/dom/dom.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/container/container.js");
		$tpl->addJavaScript("./Services/YUI/js/2_5_0/dragdrop/dragdrop.js");
		$tpl->addCss("./Services/YUI/js/2_5_0/container/assets/container.css");
		$tpl->addCss("./Services/YUI/templates/default/tpl.simpledialog.css");
	}
	
	static function addYesNoDialog($dialogname, $headertext, $message, $yesaction, $noaction, $defaultyes, $icon = "help")
	{
		global $tpl, $lng;
		
		ilYuiUtil::initSimpleDialog();
		
		$template = new ilTemplate("tpl.yes_no_dialog.js", TRUE, TRUE, "Services/YUI");
		$template->setVariable("DIALOGNAME", $dialogname);
		$template->setVariable("YES_ACTION", $yesaction);
		$template->setVariable("NO_ACTION", $noaction);
		$template->setVariable("DIALOG_HEADER", $headertext);
		$template->setVariable("DIALOG_MESSAGE", $message);
		$template->setVariable("TEXT_YES", $lng->txt("yes"));
		$template->setVariable("TEXT_NO", $lng->txt("no"));
		switch ($icon)
		{
			case "warn":
				$template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_WARN");
				break;
			case "tip":
				$template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_TIP");
				break;
			case "info":
				$template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_INFO");
				break;
			case "block":
				$template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_BLOCK");
				break;
			case "alarm":
				$template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_ALARM");
				break;
			case "help":
			default:
				$template->setVariable("ICON", "YAHOO.widget.SimpleDialog.ICON_HELP");
				break;
		}
		if ($defaultyes)
		{
			$template->touchBlock("isDefaultYes");
		}
		else
		{
			$template->touchBlock("isDefaultNo");
		}
		$tpl->setCurrentBlock("HeadContent");
		$tpl->setVariable("CONTENT_BLOCK", $template->get());
		$tpl->parseCurrentBlock();
	}
} // END class.ilUtil
?>
