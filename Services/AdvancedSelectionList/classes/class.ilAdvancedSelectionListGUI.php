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
	
	const DOWN_ARROW_LIGHT = "mm_down_arrow.gif";
	const DOWN_ARROW_DARK = "mm_down_arrow_dark.gif";
	const NO_ICON = "";

	/**
	* Constructor.
	*	
	*/
	public function __construct()
	{
		$this->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
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
	function addItem($a_title, $a_value = "", $a_link = "", $a_img = "", $a_alt = "", $a_frame = "")
	{
		$this->items[] = array("title" => $a_title, "value" => $a_value,
			"link" => $a_link, "img" => $a_img, "alt" => $a_alt, "frame" => $a_frame);
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
	* Set Selection Header Class.
	*
	* @param	string	$a_selectionheaderclass	Selection Header Class
	*/
	function setSelectionHeaderClass($a_selectionheaderclass)
	{
		$this->selectionheaderclass = $a_selectionheaderclass;
	}

	/**
	* Get Selection Header Class.
	*
	* @return	string	Selection Header Class
	*/
	function getSelectionHeaderClass()
	{
		return $this->selectionheaderclass;
	}

	/**
	* Set Header Icon.
	*
	* @param	string	$a_headericon	Header Icon
	*/
	function setHeaderIcon($a_headericon)
	{
		$this->headericon = $a_headericon;
	}

	/**
	* Get Header Icon.
	*
	* @return	string	Header Icon
	*/
	function getHeaderIcon()
	{
		return $this->headericon;
	}

	/**
	* Set No Javascript Link Style Class.
	*
	* @param	string	$a_nojslinkclass	No Javascript Link Style Class
	*/
	function setNoJSLinkClass($a_nojslinkclass)
	{
		$this->nojslinkclass = $a_nojslinkclass;
	}

	/**
	* Get No Javascript Link Style Class.
	*
	* @return	string	No Javascript Link Style Class
	*/
	function getNoJSLinkClass()
	{
		return $this->nojslinkclass;
	}

	/**
	* Set Item Link Class.
	*
	* @param	string	$a_itemlinkclass	Item Link Class
	*/
	function setItemLinkClass($a_itemlinkclass)
	{
		$this->itemlinkclass = $a_itemlinkclass;
	}

	/**
	* Get Item Link Class.
	*
	* @return	string	Item Link Class
	*/
	function getItemLinkClass()
	{
		return $this->itemlinkclass;
	}

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
	* Set Use Images.
	*
	* @param	boolean	$a_useimages	Use Images
	*/
	function setUseImages($a_useimages)
	{
		$this->useimages = $a_useimages;
	}

	/**
	* Get Use Images.
	*
	* @return	boolean	Use Images
	*/
	function getUseImages()
	{
		return $this->useimages;
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
				
			if ($this->getUseImages())
			{
				if ($item["img"])
				{
					$tpl->setCurrentBlock("image");
					$tpl->setVariable("IMG_ITEM", $item["img"]);
					$tpl->setVariable("ALT_ITEM", $item["alt"]);
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->touchBlock("no_image");
				}
			}
			
			if ($item["frame"])
			{
				$tpl->setCurrentBlock("frame");
				$tpl->setVariable("TARGET_ITEM", $item["frame"]);
				$tpl->parseCurrentBlock();
			}
				
			if ($this->getItemLinkClass() != "")
			{
				$tpl->setCurrentBlock("item_link_class");
				$tpl->setVariable("ITEM_LINK_CLASS", $this->getItemLinkClass());
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("item");
			$tpl->setVariable("HREF_ITEM", $item["link"]);
			$tpl->setVariable("CSS_ROW", $this->css_row);
			$tpl->setVariable("TXT_ITEM", $item["title"]);
			
			$tpl->parseCurrentBlock();
		}
	
		if ($this->getHeaderIcon() != ilAdvancedSelectionListGUI::NO_ICON)
		{
			$tpl->setCurrentBlock("top_img");
			switch ($this->getHeaderIcon())
			{
				case ilAdvancedSelectionListGUI::DOWN_ARROW_LIGHT:
					$tpl->setVariable("IMG_DOWN",
						ilUtil::getImagePath(ilAdvancedSelectionListGUI::DOWN_ARROW_LIGHT));
					break;
				case ilAdvancedSelectionListGUI::DOWN_ARROW_DARK:
					$tpl->setVariable("IMG_DOWN",
						ilUtil::getImagePath(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK));
					break;
				default:
					$tpl->setVariable("IMG_DOWN", $this->getHeaderIcon());
					break;
			}
			$tpl->setVariable("ALT_SEL_TOP", $this->getListTitle());
			$tpl->parseCurrentBlock();
		}
		
		// js section
		$tpl->setCurrentBlock("js_section");
		$tpl->setVariable("TXT_SEL_TOP", $this->getListTitle());
		$tpl->setVariable("ID", $this->getId());
		$tpl->setVariable("CLASS_SEL_TOP", $this->getSelectionHeaderClass());
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
