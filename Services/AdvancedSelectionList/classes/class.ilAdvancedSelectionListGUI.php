<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* User interface class for advanced drop-down selection lists
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id:$
*/
class ilAdvancedSelectionListGUI
{
	private $items = array();
	private $id = "asl";

	/**
	* Constructor.
	*	
	*/
	public function __construct()
	{
	}

	/**
	* Add an item
	*
	* @param	string		item title
	* @param	string		value (used for select input)
	* @param	link		href for the item
	* @param	string		image href attribute
	* @param	string		image alt attribute
	*/
	function addItem($a_title, $a_value = "", $a_link = "", $a_img = "", $a_alt = "")
	{
		$this->items[] = array("title" => $a_title, "value" => $a_value,
			"link" => $a_link, "img" => $a_img, "alt" => $a_alt);
	}
	
	/**
	* Get items
	*
	* @return	array	array of items
	*/
	function getItems()
	{
		return $this->items;
	}
	
	/**
	* Set Truncate after x items.
	*
	* @param	integer	$a_truncateafter	Truncate after x items
	*/
	function setTruncateAfter($a_truncateafter)
	{
		$this->truncateafter = $a_truncateafter;
	}

	/**
	* Get Truncate after x items.
	*
	* @return	integer	Truncate after x items
	*/
	function getTruncateAfter()
	{
		return $this->truncateafter;
	}

	/**
	* Set List Title.
	*
	* @param	string	$a_listtitle	List Title
	*/
	function setListTitle($a_listtitle)
	{
		$this->listtitle = $a_listtitle;
	}

	/**
	* Get List Title.
	*
	* @return	string	List Title
	*/
	function getListTitle()
	{
		return $this->listtitle;
	}

	/**
	* Set Submit Button Text.
	*
	* @param	string	$a_submitbuttontext	Submit Button Text
	*/
/*	function setSubmitButtonText($a_submitbuttontext)
	{
		$this->submitbuttontext = $a_submitbuttontext;
	}*/

	/**
	* Get Submit Button Text.
	*
	* @return	string	Submit Button Text
	*/
/*	function getSubmitButtonText()
	{
		return $this->submitbuttontext;
	}*/

	/**
	* Set Form Action.
	*
	* @param	string	$a_formaction	Form Action
	*/
/*	function setFormAction($a_formaction)
	{
		$this->formaction = $a_formaction;
	}*/

	/**
	* Get Form Action.
	*
	* @return	string	Form Action
	*/
/*	function getFormAction()
	{
		return $this->formaction;
	}*/

	/**
	* Set Id.
	*
	* @param	string	$a_id	Id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	function getId()
	{
		return $this->id;
	}

	/**
	* Get selection list HTML
	*/
	public function getHTML()
	{
		$items = $this->getItems();

		// do not show list, if no item is in list
		if (count($items) == 0)
		{
			return "";
		}
		
		$GLOBALS["tpl"]->addJavascript("./Services/AdvancedSelectionList/js/AdvancedSelectionList.js");

		$tpl = new ilTemplate("tpl.adv_selection_list.html", true, true,
			"Services/AdvancedSelectionList");
			
		reset($items);

		$cnt = 0;
		foreach($items as $item)
		{
			$trunc = $this->getTruncateAfter();
			if ($trunc > 0 && $cnt++ > $trunc)
			{
				break;
			}
			
			$sel_arr[$item["ref_id"]] = $item["title"];
			$this->css_row = ($this->css_row != "tblrow1_mo")
				? "tblrow1_mo"
				: "tblrow2_mo";
				
			if ($item["image"])
			{
				$tpl->setCurrentBlock("image");
				$tpl->setVariable("IMG_ITEM", $item["image"]);
				$tpl->parseCurrentBlock();
			}
				
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("HREF_ITEM", $item["link"]);
			$tpl->setVariable("CSS_ROW", $this->css_row);
			$tpl->setVariable("TXT_ITEM", $item["title"]);
			
			$tpl->setVariable("ALT_ITEM", $item["alt"]);
			$tpl->parseCurrentBlock();
		}
	
		// js section
		$tpl->setCurrentBlock("js_section");
		$tpl->setVariable("TXT_SEL_TOP", $this->getListTitle());
		$tpl->setVariable("IMG_DOWN", ilUtil::getImagePath("mm_down_arrow.gif"));
		$tpl->setVariable("ID", $this->getId());
		$tpl->parseCurrentBlock();
		
		// no js section
		$tpl->setCurrentBlock("no_js_section");
		$sel_arr = array("1" => "Test 1", "2" => "Test 2");
		$select = ilUtil::formSelect("", "url_ref_id", $sel_arr, false, true, "0", "ilEditSelect");
		$tpl->setVariable("NO_JS_CONTENT", $select);
		$tpl->parseCurrentBlock();
		//$tpl->setVariable("TXT_SUBMT", $this->getSubmitButtonText());
		//$tpl->setVariable("ACTION", $this->getFormAction());
		
		return $tpl->get();
	}
}
?>
