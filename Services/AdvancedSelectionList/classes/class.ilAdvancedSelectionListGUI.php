<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
	
	const MODE_LINKS = "links";
	const MODE_FORM_SELECT = "select";
	
	const ON_ITEM_CLICK_HREF = "href";
	const ON_ITEM_CLICK_FORM_SUBMIT = "submit";
	const ON_ITEM_CLICK_FORM_SELECT = "select";
	
	/*
	
	The modes implement the following html for non-js fallback:
	
	MODE_LINKS:
	
	<a href="...">...</a> <a href="...">...<a>

	MODE_FORM_SELECT: (form and submit tags are optional)
	
	<form id="..." class="..." method="post" action="..." target="_top">
	<select name="..."  class="..." size="0">
	<option value="...">...</option>
	...
	</select>
	<input class="ilEditSubmit" type="submit" value="Go"/>
	</form>
	
	*/

	/**
	* Constructor.
	*	
	*/
	public function __construct()
	{
		$this->mode = ilAdvancedSelectionListGUI::MODE_LINKS;
		$this->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$this->setOnClickMode(ilAdvancedSelectionListGUI::ON_ITEM_CLICK_HREF);
	}

	/**
	* Set links mode (for no js fallback)
	*/
	public function setLinksMode($a_link_class = "")
	{
		$this->mode = ilAdvancedSelectionListGUI::MODE_LINKS;
		$this->links_mode = array(
			"link_class" => $a_link_class);
	}

	/**
	* Set form mode (for no js fallback)
	*
	* Outputs form selection including sourrounding form
	*/
	public function setFormSelectMode($a_select_name, $a_select_class = "",
		$a_include_form_tag = false, $a_form_action = "", $a_form_id = "",
		$a_form_class = "", $a_form_target = "_top",
		$a_button_text = "", $a_button_class = "", $a_button_cmd = "")
	{
		$this->mode = ilAdvancedSelectionListGUI::MODE_FORM_SELECT;
		$this->form_mode = array(
			"select_name" => $a_select_name,
			"select_class" => $a_select_class,
			"include_form_tag" => $a_include_form_tag,
			"form_action" => $a_form_action,
			"form_id" => $a_form_id,
			"form_class" => $a_form_class,
			"form_target" => $a_form_target,
			"button_text" => $a_button_text,
			"button_class" => $a_button_class,
			"button_cmd" => $a_button_cmd
			);
	}

	/**
	* Add an item
	*
	* @param	string		item title
	* @param	string		value (used for select input)
	* @param	link		href for the item
	* @param	string		image href attribute
	* @param	string		image alt attribute
	* @param	string		frame target
	* @param	string		item html (is used instead of title if js is active)
	*/
	function addItem($a_title, $a_value = "", $a_link = "", $a_img = "", $a_alt = "", $a_frame = "",
		$a_html = "")
	{
		$this->items[] = array("title" => $a_title, "value" => $a_value,
			"link" => $a_link, "img" => $a_img, "alt" => $a_alt, "frame" => $a_frame,
			"html" => $a_html);
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
	* Set access key
	*
	* @param	integer		access function id
	*/
	function setAccessKey($a_val)
	{
		$this->access_key = $a_val;
	}
	
	/**
	* Get access key
	*
	* @return	integer		access key function id
	*/
	function getAccessKey()
	{
		return $this->access_key;
	}
	
	/**
	* Set "onClick"- Mode
	*
	* Valid values are:
	* ilAdvancedSelectionList::ON_ITEM_CLICK_HREF or
	* ilAdvancedSelectionList::ON_ITEM_CLICK_FORM_SUBMIT
	* ilAdvancedSelectionList::ON_ITEM_CLICK_FORM_SELECT
	*
	* @param	string		mode
	*/
	function setOnClickMode($a_val, $a_onclick_form_id = "")
	{
		$this->on_click = $a_val;
		$this->on_click_form_id = $a_onclick_form_id;
	}
	
	/**
	* Get "onClick"-Mode
	*
	* @return	
	*/
	function getOnClickMode()
	{
		return $this->on_click;
	}
	
	/**
	* Set selected value
	*
	* @param	string		selected value
	*/
	function setSelectedValue($a_val)
	{
		$this->selected_value = $a_val;
	}
	
	/**
	* Get selected value
	*
	* @return	string		selected value
	*/
	function getSelectedValue()
	{
		return $this->selected_value;
	}
	
	/**
	* Set additional toggle element
	*
	* @param	string		element id
	* @param	string		class for "on"
	*/
	function setAdditionalToggleElement($a_el, $a_on)
	{
		$this->toggle = array("el" => $a_el, "class_on" => $a_on);
	}
	
	/**
	* Get additional toggle element
	*
	* @return	array
	*/
	function getAdditionalToggleElement()
	{
		return $this->toggle;
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
			if ($this->getOnClickMode() ==
				ilAdvancedSelectionListGUI::ON_ITEM_CLICK_HREF)
			{
				$tpl->setVariable("ONCLICK_ITEM",
					'onclick="parent.location='."'".$item["link"]."';".'"');
			}
			else if ($this->getOnClickMode() ==
				ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SUBMIT)
			{
				$tpl->setVariable("ONCLICK_ITEM",
					'onclick="ilAdvSelListFormSubmit(\''.$this->getId().'\''.
						", '".$this->form_mode["select_name"]."','".$item["value"]."',".
						"'".$this->on_click_form_id."','".$this->form_mode["button_cmd"]."');\"");
			}
			else if ($this->getOnClickMode() ==
				ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SELECT)
			{
				$tpl->setVariable("ONCLICK_ITEM",
					'onclick="ilAdvSelListFormSelect(\''.$this->getId().'\''.
						", '".$this->form_mode["select_name"]."','".$item["value"]."',".
						"'".$item["title"]."');\"");
			}

			$tpl->setVariable("CSS_ROW", $this->css_row);
			if ($item["html"] == "")
			{
				$tpl->setVariable("TXT_ITEM", $item["title"]);
			}
			else
			{
				$tpl->setVariable("TXT_ITEM", $item["html"]);
			}
			
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
		
		// output hidden input, if click mode is form submission
		if ($this->getOnClickMode() == ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SUBMIT)
		{
			$tpl->setCurrentBlock("hidden_input");
			$tpl->setVariable("HID", $this->getId());
			$tpl->parseCurrentBlock();
		}

		// output hidden input and initialize
		if ($this->getOnClickMode() == ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SELECT)
		{
			$tpl->setCurrentBlock("hidden_input");
			$tpl->setVariable("HID", $this->getId());
			$tpl->parseCurrentBlock();
			
			// init hidden input with selected value
			$tpl->setCurrentBlock("init_hidden_input");
			$tpl->setVariable("H2ID", $this->getId());
			$tpl->setVariable("HID_NAME", $this->form_mode["select_name"]);
			$tpl->setVariable("HID_VALUE", $this->getSelectedValue());
			$tpl->parseCurrentBlock();
		}
		
		// js section
		$tpl->setCurrentBlock("js_section");
		if ($this->getAccessKey() > 0)
		{
			include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
			$tpl->setVariable("ACCKEY", ilAccessKeyGUI::getAttribute($this->getAccessKey()));
		}
		$toggle = $this->getAdditionalToggleElement();
		if (is_array($toggle))
		{
			$tpl->setVariable("TOGGLE_OPTIONS", "{toggle_el: '".$toggle["el"]."', toggle_class_on: '".
				$toggle["class_on"]."'}");
		}
		else
		{
			$tpl->setVariable("TOGGLE_OPTIONS", "null");
		}

		$tpl->setVariable("TXT_SEL_TOP", $this->getListTitle());
		$tpl->setVariable("ID", $this->getId());
		$tpl->setVariable("CLASS_SEL_TOP", $this->getSelectionHeaderClass());
		$tpl->parseCurrentBlock();
		
		// no js sections
		switch ($this->mode)
		{
			// links mode
			case ilAdvancedSelectionListGUI::MODE_LINKS:
				reset($items);
				$cnt = 0;
				foreach($items as $item)
				{
					$tpl->setCurrentBlock("no_js_link");
					$tpl->setVariable("LINKS_CLASS", $this->links_mode["link_class"]);
					$tpl->setVariable("LINKS_HREF", $item["link"]);
					$tpl->setVariable("LINKS_TXT", $item["title"]);
					$tpl->parseCurrentBlock();
					$tpl->setCurrentBlock("no_js_section");
					$tpl->parseCurrentBlock();
				}
				break;
				
			case ilAdvancedSelectionListGUI::MODE_FORM_SELECT:
				reset($items);
				$cnt = 0;
				foreach($items as $item)
				{
					$tpl->setCurrentBlock("no_js_form_option");
					$tpl->setVariable("FRM_OPTION_TXT", $item["title"]);
					$tpl->setVariable("FRM_OPTION_VAL", $item["value"]);
					if ($this->getSelectedValue() == $item["value"])
					{
						$tpl->setVariable("SELECTED", ' selected="selected" ');
					}
					$tpl->parseCurrentBlock();
				}
				if ($this->form_mode["include_form_tag"])
				{
					$tpl->setCurrentBlock("no_js_form_begin");
					$tpl->setVariable("FRM_ID", $this->form_mode["form_id"]);
					$tpl->setVariable("FRM_CLASS", $this->form_mode["form_class"]);
					$tpl->setVariable("FRM_ACTION", $this->form_mode["form_action"]);
					$tpl->setVariable("FRM_TARGET", $this->form_mode["form_target"]);
					$tpl->parseCurrentBlock();
					$tpl->touchBlock("no_js_form_end");
				}
				if ($this->form_mode["button_text"])
				{
					$tpl->setCurrentBlock("no_js_form_button");
					$tpl->setVariable("FRM_BT_TXT", $this->form_mode["button_text"]);
					$tpl->setVariable("FRM_BT_CLASS", $this->form_mode["button_class"]);
					if ($this->form_mode["button_cmd"] != "")
					{
						$tpl->setVariable("FRM_BT_CMD", 'name="cmd['.$this->form_mode["button_cmd"].']"');
					}
					$tpl->parseCurrentBlock();
				}
				$tpl->setVariable("FRM_SELECT_NAME", $this->form_mode["select_name"]);
				$tpl->setVariable("FRM_SELECT_CLASS", $this->form_mode["select_class"]);
				
				if ($this->getAccessKey() > 0)
				{
					include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
					$tpl->setVariable("ACCKEYNJS", ilAccessKeyGUI::getAttribute($this->getAccessKey()));
				}
				
				$tpl->setCurrentBlock("no_js_section");
				$tpl->parseCurrentBlock();
				break;

		}
		
		return $tpl->get();
	}
}
?>
