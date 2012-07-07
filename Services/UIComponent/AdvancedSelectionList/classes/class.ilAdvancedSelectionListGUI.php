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
	private $asynch = false;
	
	const DOWN_ARROW_LIGHT = "mm_down_arrow.png";
	const DOWN_ARROW_DARK = "mm_down_arrow_dark.png";
	const DOWN_ARROW_TOPBAR = "mm_down_arrow_topbar.png";
	const NO_ICON = "";
	
	const MODE_LINKS = "links";
	const MODE_FORM_SELECT = "select";
	
	const ON_ITEM_CLICK_HREF = "href";
	const ON_ITEM_CLICK_FORM_SUBMIT = "submit";
	const ON_ITEM_CLICK_FORM_SELECT = "select";
	const ON_ITEM_CLICK_NOP = "nop";
	
	protected $css_row = "";
	protected $access_key = false;
	protected $toggle = false;
	protected $asynch_url = false;
	protected $selected_value = "";
	protected $trigger_event = "click";
	protected $auto_hide = false;
	
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
		$a_html = "", $a_prevent_background_click = false, $a_onclick = "", $a_ttip = "",
		$a_tt_my = "right center", $a_tt_at = "left center", $a_tt_use_htmlspecialchars = true)
	{
		$this->items[] = array("title" => $a_title, "value" => $a_value,
			"link" => $a_link, "img" => $a_img, "alt" => $a_alt, "frame" => $a_frame,
			"html" => $a_html, "prevent_background_click" => $a_prevent_background_click,
			"onclick" => $a_onclick, "ttip" => $a_ttip, "tt_my" => $a_tt_my, "tt_at" => $a_tt_at,
			"tt_use_htmlspecialchars" => $a_tt_use_htmlspecialchars);
	}

	public function flush()
	{
		$this->items = array();
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
	 * Set selection header span class
	 *
	 * @param string $a_val header span class	
	 */
	function setSelectionHeaderSpanClass($a_val)
	{
		$this->sel_head_span_class = $a_val;
	}
	
	/**
	 * Get selection header span class
	 *
	 * @return string header span class
	 */
	function getSelectionHeaderSpanClass()
	{
		return $this->sel_head_span_class;
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
	 * Set trigger event
	 */
	public function setTriggerEvent($a_val)
	{
		$this->trigger_event = $a_val;
	}

	/**
	 * Get trigger event
	 */
	public function getTriggerEvent()
	{
		return $this->trigger_event;
	}

	/**
	 * Set auto hide
	 */
	public function setAutoHide($a_val)
	{
		$this->auto_hide = $a_val;
	}

	/**
	 * Get auto hide
	 */
	public function getAutoHide()
	{
		return $this->auto_hide;
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
	* Set asynch mode (this is set to true, if list should get items asynchronously)
	*
	* @param	boolean		turn asynch mode on/off
	*/
	function setAsynch($a_val)
	{
		if ($a_val)
		{
			include_once("./Services/YUI/classes/class.ilYuiUtil.php");
			ilYuiUtil::initConnection();
		}
		$this->asynch = $a_val;
	}
	
	/**
	* Get asynch mode
	*
	* @return	boolean		turn asynch mode on/off
	*/
	function getAsynch()
	{
		return $this->asynch;
	}
	
	/**
	* Set asynch url
	*
	* @param	string		asynch url
	*/
	function setAsynchUrl($a_val)
	{
		$this->asynch_url = $a_val;
	}
	
	/**
	* Get asynch url
	*
	* @return	string		asynch url
	*/
	function getAsynchUrl()
	{
		return $this->asynch_url;
	}

	/**
	 * Set select callback
	 */
	public function setSelectCallback($a_val)
	{
		$this->select_callback = $a_val;
	}

	/**
	 * Get select callback
	 */
	public function getSelectCallback()
	{
		return $this->select_callback;
	}

	/**
	* Get selection list HTML
	*/
	public function getHTML($a_only_cmd_list_asynch = false)
	{
		$items = $this->getItems();

		// do not show list, if no item is in list
		if (count($items) == 0 && !$this->getAsynch())
		{
			return "";
		}

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initOverlay();
		$GLOBALS["tpl"]->addJavascript("./Services/UIComponent/Overlay/js/ilOverlay.js");
		$GLOBALS["tpl"]->addJavascript("./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js");
		$tpl = new ilTemplate("tpl.adv_selection_list.html", true, true,
			"Services/UIComponent/AdvancedSelectionList", "DEFAULT", false, true);
			
		reset($items);

		$cnt = 0;

		if ($this->getAsynch())
		{
			$tpl->setCurrentBlock("asynch_request");
			$tpl->setVariable("IMG_LOADER", ilUtil::getImagePath("loader.gif"));
			$tpl->parseCurrentBlock();
		}
		else
		{
			foreach($items as $item)
			{
				if (isset($item["ref_id"]))
				{
					$sel_arr[$item["ref_id"]] = (isset($item["title"]))
						? $item["title"]
						: "";
				}
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
				
				if ($this->getOnClickMode() ==
					ilAdvancedSelectionListGUI::ON_ITEM_CLICK_HREF ||
					$this->getItemLinkClass() != "")
				{
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

					$tpl->setCurrentBlock("href_s");
					$tpl->setVariable("HREF_ITEM",'href="'.$item["link"].'"');
					$tpl->setVariable("ID_ITEM", $this->getId()."_".$item["value"]);
					$tpl->parseCurrentBlock();
					
					$tpl->touchBlock("href_e");

				}

				$tpl->setCurrentBlock("item");
				if ($this->getOnClickMode() ==
					ilAdvancedSelectionListGUI::ON_ITEM_CLICK_HREF)
				{
					if ($item["prevent_background_click"])
					{
						$tpl->setVariable("ONCLICK_ITEM",'');
					}
					else
					{
						if ($item["onclick"] == "")
						{
							$tpl->setVariable("ONCLICK_ITEM",
								'onclick="'."return il.AdvancedSelectionList.openTarget('".$item["link"]."','".$item["frame"]."');".'"');
						}
						else
						{
							$tpl->setVariable("ONCLICK_ITEM",
								'onclick="'."return ".$item["onclick"].";".'"');
						}
					}

				}
				else if ($this->getOnClickMode() ==
					ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SUBMIT)
				{
					$tpl->setVariable("ONCLICK_ITEM",
						'onclick="return il.AdvancedSelectionList.submitForm(\''.$this->getId().'\''.
							", '".$this->form_mode["select_name"]."','".$item["value"]."',".
							"'".$this->on_click_form_id."','".$this->form_mode["button_cmd"]."');\"");
				}
				else if ($this->getOnClickMode() ==
					ilAdvancedSelectionListGUI::ON_ITEM_CLICK_FORM_SELECT)
				{
					$tpl->setVariable("ONCLICK_ITEM",
						'onclick="return il.AdvancedSelectionList.selectForm(\''.$this->getId().'\''.
							", '".$this->form_mode["select_name"]."','".$item["value"]."',".
							"'".$item["title"]."');\"");
				}
				else if ($this->getOnClickMode() ==
					ilAdvancedSelectionListGUI::ON_ITEM_CLICK_NOP)
				{
					$tpl->setVariable("ONCLICK_ITEM",
						'onclick="il.AdvancedSelectionList.clickNop(\''.$this->getId().'\''.
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

				$tpl->setVariable("ID_ITEM_TR", $this->getId()."_".$item["value"]."_tr");
				if ($item["ttip"] != "")
				{
					include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
					ilTooltipGUI::addTooltip($this->getId()."_".$item["value"]."_tr", $item["ttip"],
						"", $item["tt_my"], $item["tt_at"], $item["tt_use_htmlspecialchars"]);
				}
				
				$tpl->parseCurrentBlock();

				// add item to js object
				$tpl->setCurrentBlock("js_item");
				$tpl->setVariable("IT_ID", $this->getId());
				$tpl->setVariable("IT_HID_NAME", $this->form_mode["select_name"]);
				$tpl->setVariable("IT_HID_VAL", $item["value"]);
				$tpl->setVariable("IT_TITLE", str_replace("'", "\\'", $item["title"]));
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setCurrentBlock("cmd_table");
		$tpl->parseCurrentBlock();
		
		if ($a_only_cmd_list_asynch)
		{
			return $tpl->get("cmd_table");
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
				case ilAdvancedSelectionListGUI::DOWN_ARROW_TOPBAR:
					$tpl->setVariable("IMG_DOWN",
						ilUtil::getImagePath(ilAdvancedSelectionListGUI::DOWN_ARROW_TOPBAR));
					break;
				default:
					$tpl->setVariable("IMG_DOWN", $this->getHeaderIcon());
					break;
			}
			// do not repeat title (accessibility) -> empty alt
			//$tpl->setVariable("ALT_SEL_TOP", $this->getListTitle());
			$tpl->setVariable("ALT_SEL_TOP", "");
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

		$cfg["trigger_event"] = $this->getTriggerEvent();
		$cfg["auto_hide"] = $this->getAutoHide();

		if ($this->getSelectCallback() != "")
		{
			$cfg["select_callback"] = $this->getSelectCallback();
		}
		$cfg["anchor_id"] = "ilAdvSelListAnchorElement_".$this->getId();
		$cfg["asynch"] = $this->getAsynch()
			? true
			: false;
		$cfg["asynch_url"] = $this->getAsynchUrl();
		$toggle = $this->getAdditionalToggleElement();
		if (is_array($toggle))
		{
			$cfg["toggle_el"] = $toggle["el"];
			$cfg["toggle_class_on"] = $toggle["class_on"];
		}
//echo "<br>".htmlentities($this->getAsynchUrl());
		include_once("./Services/JSON/classes/class.ilJsonUtil.php");
		$tpl->setVariable("TXT_SEL_TOP", $this->getListTitle());
		$tpl->setVariable("ID", $this->getId());
		$tpl->setVariable("CFG", ilJsonUtil::encode($cfg));
//echo htmlentities(ilJsonUtil::encode($cfg));
		$tpl->setVariable("CLASS_SEL_TOP", $this->getSelectionHeaderClass());
		if ($this->getSelectionHeaderSpanClass() != "")
		{
			$tpl->setVariable("CLASS_SEL_TOP_SPAN",
				$this->getSelectionHeaderSpanClass());
		}

		// set the async url to an extra template variable
		// (needed for a mobile skin)
		$tpl->setVariable("ASYNC_URL", $this->getAsynchUrl());

		$tpl->parseCurrentBlock();
		
		// no js sections
if (false)
{
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
}

		return $tpl->get();
	}
}
?>
