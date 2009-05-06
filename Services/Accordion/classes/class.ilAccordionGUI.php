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
* Accordion user interface class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id:$
*/
class ilAccordionGUI
{
	protected $items = array();
	protected static $accordion_cnt = 0;
	const VERTICAL = "vertical";
	const HORIZONTAL = "horizontal";
	
	/**
	* Constructor
	*/
	function __construct()
	{
		$this->setOrientation(ilAccordionGUI::VERTICAL);
	}
	
	/**
	* Set Orientation.
	*
	* @param	string	$a_orientation	Orientation
	*/
	function setOrientation($a_orientation)
	{
		if (in_array($a_orientation,
			array(ilAccordionGUI::VERTICAL, ilAccordionGUI::HORIZONTAL)))
		{
			$this->orientation = $a_orientation;
		}
	}

	/**
	* Get Orientation.
	*
	* @return	string	Orientation
	*/
	function getOrientation()
	{
		return $this->orientation;
	}

	/**
	* Set Container CSS Class.
	*
	* @param	string	$a_containerclass	Container CSS Class
	*/
	function setContainerClass($a_containerclass)
	{
		$this->containerclass = $a_containerclass;
	}

	/**
	* Get Container CSS Class.
	*
	* @return	string	Container CSS Class
	*/
	function getContainerClass()
	{
		return $this->containerclass;
	}

	/**
	* Set Header CSS Class.
	*
	* @param	string	$a_headerclass	Header CSS Class
	*/
	function setHeaderClass($a_headerclass)
	{
		$this->headerclass = $a_headerclass;
	}

	/**
	* Get Header CSS Class.
	*
	* @return	string	Header CSS Class
	*/
	function getHeaderClass()
	{
		return $this->headerclass;
	}

	/**
	* Set Content CSS Class.
	*
	* @param	string	$a_contentclass	Content CSS Class
	*/
	function setContentClass($a_contentclass)
	{
		$this->contentclass = $a_contentclass;
	}

	/**
	* Get Content CSS Class.
	*
	* @return	string	Content CSS Class
	*/
	function getContentClass()
	{
		return $this->contentclass;
	}

		/**
	* Set ContentWidth.
	*
	* @param	integer	$a_contentwidth	ContentWidth
	*/
	function setContentWidth($a_contentwidth)
	{
		$this->contentwidth = $a_contentwidth;
	}

	/**
	* Get ContentWidth.
	*
	* @return	integer	ContentWidth
	*/
	function getContentWidth()
	{
		return $this->contentwidth;
	}

	/**
	* Set ContentHeight.
	*
	* @param	integer	$a_contentheight	ContentHeight
	*/
	function setContentHeight($a_contentheight)
	{
		$this->contentheight = $a_contentheight;
	}

	/**
	* Get ContentHeight.
	*
	* @return	integer	ContentHeight
	*/
	function getContentHeight()
	{
		return $this->contentheight;
	}

	/**
	* Add javascript files that are necessary to run accordion
	*/
	static function addJavaScript()
	{
		global $tpl;
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initEvent();
		ilYuiUtil::initDom();
		ilYuiUtil::initAnimation();
		$tpl->addJavaScript("./Services/Accordion/js/accordion.js");
	}
	
	/**
	* Add required css
	*/
	static function addCss()
	{
		global $tpl;
		
		$tpl->addCss("./Services/Accordion/css/accordion.css");
	}

	/**
	* Add item
	*/
	function addItem($a_header, $a_content)
	{
		$this->items[] = array("header" => $a_header,
			"content" => $a_content);
	}
	
	/**
	* Get all items
	*/
	function getItems()
	{
		return $this->items;
	}
	
	/**
	* Get accordion html
	*/
	function getHTML()
	{
		self::$accordion_cnt++;
		
		$or_short = ($this->getOrientation() == ilAccordionGUI::HORIZONTAL)
			? "H"
			: "V";
			
		$width = (int) $this->getContentWidth();
		$height = (int) $this->getContentHeight();
		if ($this->getOrientation() == ilAccordionGUI::HORIZONTAL)
		{
			if ($width == 0)
			{
				$width = 200;
			}
			if ($height == 0)
			{
				$height = 100;
			}
		}
		
		$this->addJavascript();
		$this->addCss();
		
		$tpl = new ilTemplate("tpl.accordion.html", true, true, "Services/Accordion");
		foreach ($this->getItems() as $item)
		{
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("HEADER", $item["header"]);
			$tpl->setVariable("CONTENT", $item["content"]);
			$tpl->setVariable("HEADER_CLASS", $this->getHeaderClass()
				? $this->getHeaderClass() : "il_".$or_short."AccordionHead");
			$tpl->setVariable("CONTENT_CLASS", $this->getContentClass()
				? $this->getContentClass() : "il_".$or_short."AccordionContent");
			$tpl->setVariable("OR_SHORT", $or_short);

			if ($height > 0)
			{
				$tpl->setVariable("HEIGHT", "height:".$height."px;");
			}
			if ($height > 0 && $this->getOrientation() == ilAccordionGUI::HORIZONTAL)
			{
				$tpl->setVariable("HHEIGHT", "height:".$height."px;");
			}
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("CNT", self::$accordion_cnt);
		$tpl->setVariable("CONTAINER_CLASS", $this->getContainerClass()
			? $this->getContainerClass() : "il_".$or_short."AccordionContainer");
		$tpl->setVariable("ORIENTATION", $this->getOrientation());
		$tpl->setVariable("OR2_SHORT", $or_short);
		if ($width > 0)
		{
			$tpl->setVariable("WIDTH", $width);
		}
		else
		{
			$tpl->setVariable("WIDTH", "null");
		}
		if ($width > 0 && $this->getOrientation() == ilAccordionGUI::VERTICAL)
		{
			$tpl->setVariable("CWIDTH", 'style="width:'.$width.'px;"');
		}

		return $tpl->get();
	}
	
}
?>
