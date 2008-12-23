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

require_once("Services/Table/classes/class.ilTableGUI.php");

/**
* Class ilTable2GUI
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id: class.ilTableGUI.php 12818 2006-12-10 13:14:43Z akill $
*
* @ingroup ServicesTable
*/
class ilTable2GUI extends ilTableGUI
{
	protected $close_command = "";
	private $unique_id;
	private $headerHTML;
	
	/**
	* Constructor
	*
	*/
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilUser;
		
		parent::__construct(0, false);
		$this->unique_id = md5(uniqid());
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		$this->buttons = array();
		$this->header_commands = array();
		$this->multi = array();
		$this->formname = "table_" . $this->unique_id;
		$this->tpl = new ilTemplate("tpl.table2.html", true, true, "Services/Table");
		
		$this->setLimit($ilUser->getPref("hits_per_page"));
	}
	
	/**
	* Get parent object
	*
	* @return	object		parent GUI object
	*/
	public function getParentObject()
	{
	 	return $this->parent_obj;
	}
	
	/**
	* Get parent command
	*
	* @return	string		get parent gui object default command
	*/
	public function getParentCmd()
	{
	 	return $this->parent_cmd;
	}

	/**
	* Set text for an empty table.
	*
	* @param	string	$a_text	Text
	*/
	function setNoEntriesText($a_text)
	{
		$this->noentriestext = $a_text;
	}

	/**
	* Get text for an empty table.
	*
	* @return	string	Text
	*/
	function getNoEntriesText()
	{
		return $this->noentriestext;
	}

	/**
	* Set Enable Title.
	*
	* @param	boolean	$a_enabletitle	Enable Title
	*/
	function setEnableTitle($a_enabletitle)
	{
		$this->enabled["title"] = $a_enabletitle;
	}

	/**
	* Get Enable Title.
	*
	* @return	boolean	Enable Title
	*/
	function getEnableTitle()
	{
		return $this->enabled["title"];
	}

	/**
	* Set Enable Header.
	*
	* @param	boolean	$a_enableheader	Enable Header
	*/
	function setEnableHeader($a_enableheader)
	{
		$this->enabled["header"] = $a_enableheader;
	}

	/**
	* Get Enable Header.
	*
	* @return	boolean	Enable Header
	*/
	function getEnableHeader()
	{
		return $this->enabled["header"];
	}

	final public function setTitle($a_title, $a_icon = 0, $a_icon_alt = 0)
	{
		parent::setTitle($a_title, $a_icon, $a_icon_alt);
	}
	
	/**
	* set order column
	*
	* @param	string	(array) field name for ordering
	*/
	function setOrderField($a_order_field)
	{
		$this->order_field = $a_order_field;
	}

	function getOrderField()
	{
		return $this->order_field;
	}

	final public function setData($a_data)
	{
		$this->row_data = $a_data;
	}
	
	final public function getData()
	{
		return $this->row_data;
	}
	
	final public function dataExists()
	{
		if (is_array($this->row_data))
		{
			if (count($this->row_data) > 0)
			{
				return true;
			}
		}
		return false;
	}

	final public function setPrefix($a_prefix)
	{
		$this->prefix = $a_prefix;
	}

	/**
	* Set Form action parameter.
	*
	* @param	string	$a_form_action	Form action
	*/
	final public function setFormAction($a_form_action)
	{
		$this->form_action = $a_form_action;
	}
	
	/**
	* Get Form action parameter.
	*
	* @return	string	Form action
	*/
	final public function getFormAction()
	{
		return $this->form_action;
	}
	
	/**
	* Set Form name.
	*
	* @param	string	$a_formname	Form name
	*/
	function setFormName($a_formname)
	{
		$this->formname = $a_formname;
	}

	/**
	* Get Form name.
	*
	* @return	string	Form name
	*/
	function getFormName()
	{
		return $this->formname;
	}
	
	/**
	* Get the name of the checkbox that should be toggled with a select all button
	*
	* @return	string	name of the checkbox
	*/
	function getSelectAllCheckbox()
	{
		return $this->select_all_checkbox;
	}
	
	/**
	* Set the name of the checkbox that should be toggled with a select all button
	*
	* @param	string	$a_select_all_checkbox name of the checkbox
	*/
	function setSelectAllCheckbox($a_select_all_checkbox)
	{
		$this->select_all_checkbox = $a_select_all_checkbox;
	}

	/**
	* Set row template.
	*
	* @param	string	$a_template			Template file name.
	* @param	string	$a_template_dir		Service/Module directory.
	*/
	final public function setRowTemplate($a_template, $a_template_dir = "")
	{
		$this->row_template = $a_template;
		$this->row_template_dir = $a_template_dir;
	}
	
	/**
	* Set Default order field.
	*
	* @param	string	$a_defaultorderfield	Default order field
	*/
	function setDefaultOrderField($a_defaultorderfield)
	{
		$this->defaultorderfield = $a_defaultorderfield;
	}

	/**
	* Get Default order field.
	*
	* @return	string	Default order field
	*/
	function getDefaultOrderField()
	{
		return $this->defaultorderfield;
	}

	/**
	* Set Default order direction.
	*
	* @param	string	$a_defaultorderdirection	Default order direction
	*/
	function setDefaultOrderDirection($a_defaultorderdirection)
	{
		$this->defaultorderdirection = $a_defaultorderdirection;
	}

	/**
	* Get Default order direction.
	*
	* @return	string	Default order direction
	*/
	function getDefaultOrderDirection()
	{
		return $this->defaultorderdirection;
	}
	
	/**
	* Add Command button
	*
	* @param	string	Command
	* @param	string	Text
	*/
	function addCommandButton($a_cmd, $a_text)
	{
		$this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text);
	}
	
	
	/**
	* Add command for closing table.
	*
	* @param	string	$a_link		closing link
	*/
	function setCloseCommand($a_link)
	{
		$this->close_command = $a_link;
	}

	/**
	* Add Command button
	*
	* @param	string	Command
	* @param	string	Text
	*/
	function addMultiCommand($a_cmd, $a_text)
	{
		$this->multi[] = array("cmd" => $a_cmd, "text" => $a_text);
	}

	/**
	* Add Header Command (Link)  (Image needed for now)
	*
	* @param	string	href
	* @param	string	text
	*/
	function addHeaderCommand($a_href, $a_text, $a_target = "", $a_img = "")
	{
		$this->header_commands[] = array("href" => $a_href, "text" => $a_text,
			"target" => $a_target, "img" => $a_img);
	}

	/**
	* Add a column to the header.
	*
	* @param	string		Text
	* @param	string		Sort field name (corresponds to data array field)
	* @param	string		Width string
	*/
	final public function addColumn($a_text, $a_sort_field = "", $a_width = "")
	{
		$this->column[] = array(
			"text" => $a_text,
			"sort_field" => $a_sort_field,
			"width" => $a_width);
		$this->column_count = count($this->column);
	}
	
	final public function getNavParameter()
	{
		return $this->prefix."_table_nav";
	}
	
	function setOrderLink($sort_field, $order_dir)
	{
		global $ilCtrl;
		
		$old = $_GET[$this->getNavParameter()];
		
		// set order link
		$ilCtrl->setParameter($this->parent_obj,
			$this->getNavParameter(),
			$sort_field.":".$order_dir.":".$this->offset);
		$this->tpl->setVariable("TBL_ORDER_LINK",
			$ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
		
		// set old value of nav variable
		$ilCtrl->setParameter($this->parent_obj,
			$this->getNavParameter(), $old);
	}

	function fillHeader()
	{
		foreach ($this->column as $column)
		{
			if (!$this->enabled["sort"] || $column["sort_field"] == "")
			{
				$this->tpl->setCurrentBlock("tbl_header_no_link");
				if ($column["width"] != "")
				{
					$this->tpl->setVariable("TBL_COLUMN_WIDTH_NO_LINK"," width=\"".$column["width"]."\"");
				}
				$this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK",
					$column["text"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->touchBlock("tbl_header_th");
				continue;
			}
			if (($column["sort_field"] == $this->order_field) && ($this->order_direction != ""))
			{
				$this->tpl->setCurrentBlock("tbl_order_image");
				$this->tpl->setVariable("IMG_ORDER_DIR",ilUtil::getImagePath($this->order_direction."_order.gif"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_header_cell");
			$this->tpl->setVariable("TBL_HEADER_CELL", $column["text"]);
			
			// only set width if a value is given for that column
			if ($column["width"] != "")
			{
				$this->tpl->setVariable("TBL_COLUMN_WIDTH"," width=\"".$column["width"]."\"");
			}

			$lng_sort_column = $this->lng->txt("sort_by_this_column");
			$this->tpl->setVariable("TBL_ORDER_ALT",$lng_sort_column);
		
			$order_dir = "asc";

			if ($column["sort_field"] == $this->order_field)
			{ 
				$order_dir = $this->sort_order;

				$lng_change_sort = $this->lng->txt("change_sort_direction");
				$this->tpl->setVariable("TBL_ORDER_ALT",$lng_change_sort);
			}
		
			$this->setOrderLink($column["sort_field"], $order_dir);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("tbl_header_th");
		}
		
		$this->tpl->setCurrentBlock("tbl_header");
		$this->tpl->parseCurrentBlock();
	}

	
	final public function getHTML()
	{
		global $lng, $ilCtrl;
		
		$ilCtrl->saveParameter($this->getParentObject(), $this->getNavParameter());
		
		if(!$this->enabled['content'])
		{
			return $this->render();
		}

		if ($_POST[$this->getNavParameter()."1"] != "")
		{
			if ($_POST[$this->getNavParameter()."1"] != $_POST[$this->getNavParameter()])
			{
				$this->nav_value = $_POST[$this->getNavParameter()."1"];
			}
			else if ($_POST[$this->getNavParameter()."2"] != $_POST[$this->getNavParameter()])
			{
				$this->nav_value = $_POST[$this->getNavParameter()."2"];
			}
		}
		elseif($_GET[$this->getNavParameter()])
		{
			$this->nav_value = $_GET[$this->getNavParameter()];
		}
		elseif($_SESSION[$this->getNavParameter()] != "")
		{
			$this->nav_value = $_SESSION[$this->getNavParameter()];
		}
		else $this->nav_value = ':asc:0';
		
		$nav = explode(":", $this->nav_value);
		
		// $nav[0] is order by
		$this->setOrderField(($nav[0] != "") ? $nav[0] : $this->getDefaultOrderField());
		$this->setOrderDirection(($nav[1] != "") ? $nav[1] : $this->getDefaultOrderDirection());
		$this->setOffset($nav[2]);
		$this->setMaxCount(count($this->row_data));
		$this->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		
		// sort
		$data = $this->getData();
		$data = ilUtil::sortArray($data, $this->getOrderField(),
			$this->getOrderDirection(), $this->numericOrdering($this->getOrderField()));
				
		// slice
		$data = array_slice($data, $this->getOffset(), $this->getLimit());

		// fill rows
		if(count($data) > 0)
		{
			$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", $this->row_template,
				$this->row_template_dir);
	
			foreach($data as $set)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";
				$this->tpl->setVariable("CSS_ROW", $this->css_row);

				$this->fillRow($set);
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			// add standard no items text (please tell me, if it messes something up, alex, 29.8.2008)
			$no_items_text = (trim($this->getNoEntriesText()) != '')
				? $this->getNoEntriesText()
				: $lng->txt("no_items");

			$this->css_row = ($this->css_row != "tblrow1")
					? "tblrow1"
					: "tblrow2";				
			
			$this->tpl->setCurrentBlock("tbl_no_entries");
			$this->tpl->setVariable('TBL_NO_ENTRY_CSS_ROW', $this->css_row);
			$this->tpl->setVariable('TBL_NO_ENTRY_COLUMN_COUNT', $this->column_count);
			$this->tpl->setVariable('TBL_NO_ENTRY_TEXT', trim($no_items_text));
			$this->tpl->parseCurrentBlock();			
		}
		
		// set form action		
		if ($this->form_action != "")
		{
			$this->tpl->setCurrentBlock("tbl_form_header");
			$this->tpl->setVariable("FORMACTION", $this->getFormAction());
			$this->tpl->setVariable("FORMNAME", $this->getFormName());
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("tbl_form_footer");
		}
		
		$this->fillFooter();
				
		$this->fillActionRow();

		return $this->render();
	}

	/**
	* Should this field be sorted numeric?
	*
	* @return	boolean		numeric ordering; default is false
	*/
	function numericOrdering($a_field)
	{
		return false;
	}
	
	/**
	* render table
	* @access	public
	*/
	function render()
	{
		global $lng;
		
		$this->tpl->setVariable("CSS_TABLE",$this->getStyle("table"));
		
		// table title and icon
		if ($this->enabled["title"])
		{
			if ($this->enabled["icon"])
			{
				$this->tpl->setCurrentBlock("tbl_header_title_icon");
				$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath($this->icon));
				$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->icon_alt);
				$this->tpl->parseCurrentBlock();
			}
			
			foreach($this->header_commands as $command)
			{
				if ($command["img"] != "")
				{
					$this->tpl->setCurrentBlock("tbl_header_img_link");
					if ($command["target"] != "")
					{
						$this->tpl->setVariable("TARGET_IMG_LINK",
							'target="'.$command["target"].'"');
					}
					$this->tpl->setVariable("ALT_IMG_LINK", $command["text"]);
					$this->tpl->setVariable("HREF_IMG_LINK", $command["href"]);
					$this->tpl->setVariable("SRC_IMG_LINK",
						$command["img"]);
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->setCurrentBlock("head_cmd");
					$this->tpl->setVariable("TXT_HEAD_CMD", $command["text"]);
					$this->tpl->setVariable("HREF_HEAD_CMD", $command["href"]);
					$this->tpl->parseCurrentBlock();
				}
			}
			
			if (isset ($this->headerHTML)) {
				$this->tpl->setCurrentBlock("tbl_header_html");
				$this->tpl->setVariable ("HEADER_HTML", $this->headerHTML);
			    $this->tpl->parseCurrentBlock();
			}
			
			// close command
			if ($this->close_command != "")
			{
				$this->tpl->setCurrentBlock("tbl_header_img_link");
				$this->tpl->setVariable("ALT_IMG_LINK",$lng->txt("close"));
				$this->tpl->setVariable("HREF_IMG_LINK",$this->close_command);
				$this->tpl->setVariable("SRC_IMG_LINK",ilUtil::getImagePath("icon_close2.gif"));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_header_title");
			$this->tpl->setVariable("COLUMN_COUNT",$this->column_count);
			$this->tpl->setVariable("TBL_TITLE",$this->title);
			$this->tpl->parseCurrentBlock();
		}

		// table header
		if ($this->enabled["header"])
		{
			$this->fillHeader();
		}

		$this->tpl->touchBlock("tbl_table_end");

		return $this->tpl->get();
	}

	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*
	* @param	array	$a_set		data array
	*/
	protected function fillRow($a_set)
	{
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	}
	
	
	/**
	* Fill footer row
	*/
	function fillFooter()
	{
		global $lng, $ilCtrl;

		$footer = false;
		
		// select all checkbox
		if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox())) && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("select_all_checkbox");
			$this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $lng->txt("select_all"));
			$this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
			$this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
			$this->tpl->setVariable("CHECKBOXNAME", "chb_select_all_" . $this->unique_id);
			$this->tpl->parseCurrentBlock();
			$footer = true;
		}
		
		// table footer numinfo
		if ($this->enabled["numinfo"] && $this->enabled["footer"])
		{
			$start = $this->offset + 1;				// compute num info
			if (!$this->dataExists())
			{
				$start = 0;
			}
			$end = $this->offset + $this->limit;
			
			if ($end > $this->max_count or $this->limit == 0)
			{
				$end = $this->max_count;
			}
			
			if ($this->lang_support)
			{
				$numinfo = "(".$start." - ".$end." ".strtolower($this->lng->txt("of"))." ".$this->max_count.")";
			}
			else
			{
				$numinfo = "(".$start." - ".$end." of ".$this->max_count.")";
			}
			if ($this->max_count > 0)
			{
				$this->tpl->setCurrentBlock("tbl_footer_numinfo");
				$this->tpl->setVariable("NUMINFO", $numinfo);
				$this->tpl->parseCurrentBlock();
			}
			$footer = true;
		}

		// table footer linkbar
		if ($this->enabled["linkbar"] && $this->enabled["footer"] && $this->limit  != 0
			 && $this->max_count > 0)
		{
			$layout = array(
							"link"	=> $this->footer_style,
							"prev"	=> $this->footer_previous,
							"next"	=> $this->footer_next,
							);
			$linkbar = $this->getLinkbar("1");
			$this->tpl->setCurrentBlock("tbl_footer_linkbar");
			$this->tpl->setVariable("LINKBAR", $linkbar);
			$this->tpl->parseCurrentBlock();
			$footer = true;
		}

		if ($footer)
		{
			$this->tpl->setCurrentBlock("tbl_footer");
			$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
			$this->tpl->parseCurrentBlock();
			
			// top navigation, if number info or linkbar given
			if ($numinfo != "" || $linkbar != "")
			{
				if ($numinfo != "")
				{
					$this->tpl->setCurrentBlock("top_numinfo");
					$this->tpl->setVariable("NUMINFO", $numinfo);
					$this->tpl->parseCurrentBlock();
				}
				if ($linkbar != "")
				{
					$linkbar = $this->getLinkbar("2");
					$this->tpl->setCurrentBlock("top_linkbar");
					$this->tpl->setVariable("LINKBAR", $linkbar);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("top_navigation");
				$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	* Get previous/next linkbar.
	*
	* @author Sascha Hofmann <shofmann@databay.de>
	*
	* @return	array	linkbar or false on error
	*/
	function getLinkbar($a_num)
	{
		global $ilCtrl, $lng;
		
		$link = $ilCtrl->getLinkTargetByClass(get_class($this->parent_obj), $this->parent_cmd).
			"&".$this->getNavParameter()."=".
			$this->getOrderField().":".$this->getOrderDirection().":";
		
		$LinkBar = "";
		$layout_prev = $lng->txt("previous");
		$layout_next = $lng->txt("next");
		
		// if more entries then entries per page -> show link bar
		if ($this->max_count > $this->getLimit())
		{
			// previous link
			if ($this->getOffset() >= 1)
			{
				$prevoffset = $this->getOffset() - $this->getLimit();
				$LinkBar .= "<a class=\"small\" href=\"".$link.$prevoffset."\">".$layout_prev."&nbsp;</a>";
			}
			else
			{
				$LinkBar .= '<span class="il_BlockInfo">'.$layout_prev."&nbsp;</span>";
			}
			
			// current value
			if ($a_num == "1")
			{
				$LinkBar .= '<input type="hidden" name="'.$this->getNavParameter().
					'" value="'.$this->getOrderField().":".$this->getOrderDirection().":".$this->getOffset().'" />';
			}

			// calculate number of pages
			$pages = intval($this->max_count / $this->getLimit());

			// add a page if a rest remains
			if (($this->max_count % $this->getLimit()))
				$pages++;

			// links to other pages
			$offset_arr = array();
			for ($i = 1 ;$i <= $pages ; $i++)
			{
				$newoffset = $this->getLimit() * ($i-1);

				$nav_value = $this->getOrderField().":".$this->getOrderDirection().":".$newoffset;
				$offset_arr[$nav_value] = $i;
				if ($newoffset == $this->getOffset())
				{
				//	$LinkBar .= "[".$i."] ";
				}
				else
				{
				//	$LinkBar .= '<a '.$layout_link.' href="'.
				//		$link.$newoffset.'">['.$i.']</a> ';
				}
			}
			
			// show next link (if not last page)
			if (! ( ($this->getOffset() / $this->getLimit())==($pages-1) ) && ($pages!=1) )
			{
				if ($LinkBar != "")
					$LinkBar .= "<span class=\"small\" > | </span>"; 
				$newoffset = $this->getOffset() + $this->getLimit();
				$LinkBar .= "<a class=\"small\" href=\"".$link.$newoffset."\">&nbsp;".$layout_next."</a>";
			}
			else
			{
				if ($LinkBar != "")
					$LinkBar .= "<span class=\"small\" > | </span>"; 
				$LinkBar .= '<span class="il_BlockInfo">&nbsp;'.$layout_next."</span>";
			}
			
			if (count($offset_arr))
			{				
				$LinkBar .= "&nbsp;&nbsp;&nbsp;&nbsp;".ilUtil::formSelect($this->nav_value,
					$this->getNavParameter().$a_num, $offset_arr, false, true, 0, "ilEditSelect").
					' <input class="ilEditSubmit" type="submit" name="cmd['.$this->parent_cmd.']" value="'.
					$lng->txt("select_page").'" /> ';
			}

			return $LinkBar;
		}
		else
		{
			return false;
		}
	}

	/**
	* Fill Action Row.
	*/
	function fillActionRow()
	{
		global $lng;
		
		// action row
		$action_row = false;
		$arrow = false;
		
		// add buttons
		if (count($this->buttons) > 0)
		{
			foreach ($this->buttons as $button)
			{
				$this->tpl->setCurrentBlock("plain_button");
				$this->tpl->setVariable("PBTN_NAME", $button["cmd"]);
				$this->tpl->setVariable("PBTN_VALUE", $button["text"]);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("plain_buttons");
			$this->tpl->parseCurrentBlock();
			
			$action_row = true;
		}
		
		// multi selection 
		if (count($this->multi) > 1 && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("tbl_cmd_select");
			$sel = array();
			foreach ($this->multi as $mc)
			{
				$sel[$mc["cmd"]] = $mc["text"];
			}
			$this->tpl->setVariable("SELECT_CMDS",
				ilUtil::formSelect("", "selected_cmd", $sel, false, true));
			$this->tpl->setVariable("TXT_EXECUTE", $lng->txt("execute"));
			$this->tpl->setVariable('SELECT_CMD','select_cmd');
			$this->tpl->parseCurrentBlock();
			$arrow = true;
			$action_row = true;
		}
		elseif(count($this->multi) == 1  && $this->dataExists())
		{
			$this->tpl->setCurrentBlock("tbl_single_cmd");
			$sel = array();
			foreach ($this->multi as $mc)
			{
				$cmd = $mc['cmd'];
				$txt = $mc['text'];
			}
			$this->tpl->setVariable("TXT_SINGLE_CMD",$txt);
			$this->tpl->setVariable("SINGLE_CMD",$cmd);
			$this->tpl->parseCurrentBlock();
			$arrow = true;
			$action_row = true;
		}

		if ($arrow)
		{
			$this->tpl->setCurrentBlock("tbl_action_img_arrow");
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("ALT_ARROW", $lng->txt("action"));
			$this->tpl->parseCurrentBlock();
		}
		
		if ($action_row)
		{
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("COLUMN_COUNTS", $this->getColumnCount());
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * set header html
	 *
	 * @param string $html
	 */
	
	public function setHeaderHTML($html) 
	{
	    $this->headerHTML = $html;
	}
}
?>
