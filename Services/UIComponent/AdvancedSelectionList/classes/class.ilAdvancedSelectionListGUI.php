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

	const DOWN_ARROW_DARK = "down_arrow_dark";
	const ICON_ARROW = "caret";
	const ICON_CONFIG = "glyphicon glyphicon-cog";
	const NO_ICON = "";
	
	const MODE_LINKS = "links";
	const MODE_FORM_SELECT = "select";
	
	const ON_ITEM_CLICK_HREF = "href";
	const ON_ITEM_CLICK_FORM_SUBMIT = "submit";
	const ON_ITEM_CLICK_FORM_SELECT = "select";
	const ON_ITEM_CLICK_NOP = "nop";

	const STYLE_DEFAULT = 0;
	const STYLE_LINK = 1;
	const STYLE_EMPH = 2;
	const STYLE_LINK_BUTTON = 3;
	
	protected $css_row = "";
	protected $access_key = false;
	protected $toggle = false;
	protected $asynch_url = false;
	protected $selected_value = "";
	protected $trigger_event = "click";
	protected $auto_hide = false;
	protected $grouped_list = null;
	protected $style = 0;
	private $dd_pullright = true;
	
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
	
	/**
	 * Set Grouped List
	 *
	 * @param ilGroupedListGUI $a_val grouped list object	
	 */
	function setGroupedList($a_val)
	{
		$this->grouped_list = $a_val;
	}
	
	/**
	 * Get Grouped List
	 *
	 * @return ilGroupedListGUI grouped list object
	 */
	function getGroupedList()
	{
		return $this->grouped_list;
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
	 * DEPRECATED use set style instead
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
	 * Set style
	 *
	 * @param int $a_val button style STYLE_DEFAULT, STYLE_LINK, STYLE_EMPH
	 */
	function setStyle($a_val)
	{
		$this->style = $a_val;
	}
	
	/**
	 * Get style
	 *
	 * @return int button style STYLE_DEFAULT, STYLE_LINK, STYLE_EMPH
	 */
	function getStyle()
	{
		return $this->style;
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
	 * Set pull right
	 *
	 * @param bool $a_val pull right
	 */
	function setPullRight($a_val)
	{
		$this->dd_pullright = $a_val;
	}

	/**
	 * Get pull right
	 *
	 * @return bool pull right
	 */
	function getPullRight()
	{
		return $this->dd_pullright;
	}

	/**
	* Get selection list HTML
	*/
	public function getHTML($a_only_cmd_list_asynch = false)
	{
		$items = $this->getItems();

		// do not show list, if no item is in list
		if (count($items) == 0 && !$this->getAsynch() && $this->getGroupedList() == null)
		{
			return "";
		}

		/* bootstrap made this obsolete ?!
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initOverlay();
		$GLOBALS["tpl"]->addJavascript("./Services/UIComponent/Overlay/js/ilOverlay.js");					
		*/
		$GLOBALS["tpl"]->addJavascript("./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js");		 
		
		$tpl = new ilTemplate("tpl.adv_selection_list.html", true, true,
			"Services/UIComponent/AdvancedSelectionList", "DEFAULT", false, true);
			
		reset($items);

		$cnt = 0;

		if ($this->getAsynch())
		{
			$tpl->setCurrentBlock("asynch_request");
			$tpl->setVariable("IMG_LOADER", ilUtil::getImagePath("loader.svg"));
			$tpl->parseCurrentBlock();
		}
		else
		{
			if ($this->getGroupedList() != null)
			{
				$tpl->setVariable("GROUPED_LIST_HTML", $this->getGroupedList()->getHTML());
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
			}						
		}
		
		if ($a_only_cmd_list_asynch)
		{
			$tpl->touchBlock("cmd_table");
			return $tpl->get("cmd_table");
		}

		if ($this->getGroupedList() == null)
		{
			$tpl->setCurrentBlock("dd_content");
			if ($this->getPullRight())
			{
				$tpl->setVariable("UL_CLASS", "dropdown-menu pull-right");
			}
			else
			{
				$tpl->setVariable("UL_CLASS", "dropdown-menu");
			}
			$tpl->setVariable("TABLE_ID", $this->getId());
			$tpl->parseCurrentBlock();
		}

		if ($this->getHeaderIcon() != ilAdvancedSelectionListGUI::NO_ICON)
		{
			$tpl->setCurrentBlock("top_img");
			switch ($this->getHeaderIcon())
			{
				case ilAdvancedSelectionListGUI::ICON_CONFIG:
					$tpl->setVariable("IMG_SPAN_STYLE", ilAdvancedSelectionListGUI::ICON_CONFIG);
					break;

				case ilAdvancedSelectionListGUI::DOWN_ARROW_DARK:
				default:
					$tpl->setVariable("IMG_SPAN_STYLE", ilAdvancedSelectionListGUI::ICON_ARROW);
					break;
			}
			$tpl->parseCurrentBlock();
		}
		
		
		if($this->getAsynch())
		{
			$tpl->setCurrentBlock("asynch_bl");
			$tpl->setVariable("ASYNCH_URL", $this->getAsynchUrl());
			$tpl->setVariable("ASYNCH_ID", $this->getId());
			$tpl->setVariable("ASYNCH_TRIGGER_ID", $this->getId());
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
		$tpl->setVariable("CFG", ilJsonUtil::encode($cfg));		 
		 
		//echo htmlentities(ilJsonUtil::encode($cfg));	
		
		$tpl->setVariable("TXT_SEL_TOP", $this->getListTitle());
		$tpl->setVariable("ID", $this->getId());
		
		//$tpl->setVariable("CLASS_SEL_TOP", $this->getSelectionHeaderClass());
		switch ($this->getStyle())
		{
			case self::STYLE_DEFAULT:
				$tpl->setVariable("BTN_CLASS", "btn btn-sm btn-default");
				$tpl->setVariable("TAG", "button");
				break;

			case self::STYLE_EMPH:
				$tpl->setVariable("BTN_CLASS", "btn btn-sm btn-primary");
				$tpl->setVariable("TAG", "button");
				break;

			case self::STYLE_LINK_BUTTON:
				$tpl->setVariable("BTN_CLASS", "btn btn-sm btn-link");
				$tpl->setVariable("TAG", "button");
				break;

			case self::STYLE_LINK:
				$tpl->setVariable("BTN_CLASS", "");
				$tpl->setVariable("TAG", "a");
				break;
		}


		if ($this->getSelectionHeaderSpanClass() != "")
		{
			$tpl->setVariable("CLASS_SEL_TOP_SPAN",
				$this->getSelectionHeaderSpanClass());
		}

		// set the async url to an extra template variable
		// (needed for a mobile skin)
		// $tpl->setVariable("ASYNC_URL", $this->getAsynchUrl());

		$tpl->parseCurrentBlock();

		return $tpl->get();
	}
}
?>
