<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once ("Services/News/classes/class.ilNewsItemGUIGen.php");

/**
* User Interface for NewsItem entities.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNewsItemGUI extends ilNewsItemGUIGen
{

	function getHTML()
	{
		global $lng;
		
		$lng->LoadLanguageModule("news");
		
		return $this->getNewsForContextBlock();
	}
	
	/**
	* BLOCK NewsForContext: Prepare block query for news block.
	*/
	function prepareBlockQueryNewsForContext(&$a_news_item)
	{
		$a_news_item->setContextObjId($this->ctrl->getContextObjId());
		$a_news_item->setContextObjType($this->ctrl->getContextObjType());
	}
	
	/**
	* FORM NewsItem: Prepare saving.
	*/
	function prepareSaveNewsItem(&$a_news_item)
	{
		$a_news_item->setContextObjId($this->ctrl->getContextObjId());
		$a_news_item->setContextObjType($this->ctrl->getContextObjType());
	}
	
	/**
	* FORM NewsItem: Prepare form.
	*
	* @param	object	$a_form_gui	ilPropertyFormGUI instance.
	*/
	public function prepareFormNewsItem(&$a_form_gui)
	{
		$a_form_gui->setTitleIcon(ilUtil::getImagePath("icon_news.gif"));
	}

	function editNews()
	{
		//$news_item = new ilNewsItem();
		//$news_item->setContextObjId($this->ctrl->getContextObjId());
		//$news_item->setContextObjType($this->ctrl->getContextObjType());
		//$news = $news_item->queryNewsForContext();
		
		return $this->getNewsForContextTable();
	}

	function cancelUpdate()
	{
		return $this->editNews();
	}
	
	/**
	* TABLE MewsForContext: Prepare the new table
	*/
	function prepareTableNewsForContext(&$a_table_gui)
	{
		global $ilCtrl, $lng;
		
		$a_table_gui->addCommandButton("createNewsItem", $lng->txt("add"));
		$a_table_gui->setTitle($lng->txt("news"), "icon_news.gif", $lng->txt("news"));
	}
}
