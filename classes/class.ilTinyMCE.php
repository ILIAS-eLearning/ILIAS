<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Class ilTinyMCE
* functions for the integration of the tinyMCE javascript editor component
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @package ilias-core
*/

class ilTinyMCE
{
	function ilTinyMCE ()
	{
		return;
	}
	
	function _buildAdvancedBlockformatsFromHTMLTags($a_html_tags)
	{
		$blockformats = array();
		
		if (in_array("p", $a_html_tags))
		{
			array_push($blockformats, "p");
		}
		if (in_array("div", $a_html_tags))
		{
			array_push($blockformats, "div");
		}
		if (in_array("pre", $a_html_tags))
		{
			array_push($blockformats, "pre");
		}
		if (in_array("code", $a_html_tags))
		{
			array_push($blockformats, "code");
		}
		if (in_array("h1", $a_html_tags))
		{
			array_push($blockformats, "h1");
		}
		if (in_array("h2", $a_html_tags))
		{
			array_push($blockformats, "h2");
		}
		if (in_array("h3", $a_html_tags))
		{
			array_push($blockformats, "h3");
		}
		if (in_array("h4", $a_html_tags))
		{
			array_push($blockformats, "h4");
		}
		if (in_array("h5", $a_html_tags))
		{
			array_push($blockformats, "h5");
		}
		if (in_array("h6", $a_html_tags))
		{
			array_push($blockformats, "h6");
		}
		if (count($blockformats))
		{
			return join(",", $blockformats);
		}
		else
		{
			return "";
		}
	}

	function _buildAdvancedButtonsFromHTMLTags($a_html_tags)
	{
		$theme_advanced_buttons = array();
		if (in_array("strong", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "bold");
		}
		if (in_array("em", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "italic");
		}
		if (in_array("u", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "underline");
		}
		if (in_array("strike", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "strikethrough");
		}
		if (count($theme_advanced_buttons))
		{
			array_push($theme_advanced_buttons, "separator");
		}
		if (in_array("p", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "justifyleft");
			array_push($theme_advanced_buttons, "justifycenter");
			array_push($theme_advanced_buttons, "justifyright");
			array_push($theme_advanced_buttons, "justifyfull");
			array_push($theme_advanced_buttons, "separator");
		}
		if (strlen(ilTinyMCE::_buildAdvancedBlockformatsFromHTMLTags($a_html_tags)))
		{
			array_push($theme_advanced_buttons, "formatselect");
		}
		if (in_array("hr", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "hr");
		}
		array_push($theme_advanced_buttons, "removeformat");
		array_push($theme_advanced_buttons, "separator");
		if (in_array("sub", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "sub");
		}
		if (in_array("sup", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "sup");
		}
		array_push($theme_advanced_buttons, "charmap");
		if ((in_array("ol", $a_html_tags)) && (in_array("li", $a_html_tags)))
		{
			array_push($theme_advanced_buttons, "bullist");
		}
		if ((in_array("ul", $a_html_tags)) && (in_array("li", $a_html_tags)))
		{
			array_push($theme_advanced_buttons, "numlist");
		}
		array_push($theme_advanced_buttons, "separator");
		if (in_array("cite", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "cite");
		}
		if (in_array("abbr", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "abbr");
		}
		if (in_array("acronym", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "acronym");
		}
		if (in_array("del", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "del");
		}
		if (in_array("ins", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "ins");
		}
		if (in_array("blockquote", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "indent");
			array_push($theme_advanced_buttons, "outdent");
		}
		if (in_array("a", $a_html_tags))
		{
			array_push($theme_advanced_buttons, "link");
			array_push($theme_advanced_buttons, "unlink");
			array_push($theme_advanced_buttons, "anchor");
		}
		array_push($theme_advanced_buttons, "separator");
		array_push($theme_advanced_buttons, "undo");
		array_push($theme_advanced_buttons, "redo");
		return join(",", $theme_advanced_buttons);
	}
	
	function _getEditorLanguage()
	{
		global $ilUser;
		$lang = $ilUser->getLanguage();
		if (file_exists(ilUtil::getJSPath("tiny_mce/langs/$lang.js")))
		{
			return "$lang";
		}
		else
		{
			return "en";
		}
	}
}
?>
