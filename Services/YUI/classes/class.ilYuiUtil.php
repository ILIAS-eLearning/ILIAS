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
		
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/yahoo/yahoo-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/connection/connection-min.js");
	}

	/**
	* Init YUI Menu module
	*/
	static function initMenu()
	{
		global $tpl;
		
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/yahoo/yahoo-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/event/event.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/dom/dom.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/container/container_core.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/menu/menu.js");
		$tpl->addCss("./Services/YUI/js/2_2_1/menu/assets/menu.css");
	}

	/**
	* Init YUI Overlay module
	*/
	static function initOverlay()
	{
		global $tpl;
		
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/yahoo/yahoo-min.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/event/event.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/dom/dom.js");
		$tpl->addJavaScript("./Services/YUI/js/2_2_1/container/container_core.js");
	}
} // END class.ilUtil
?>
