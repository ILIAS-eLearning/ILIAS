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
	/**
	* Constructor
	*
	*/
	public function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilUser;
		
		parent::__construct(0, false);
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		$this->buttons = array();
		$this->multi = array();
		$this->formname = "table";
		$this->tpl = new ilTemplate("tpl.table2.html", true, true, "Services/Table");
		
		$this->setLimit($ilUser->getPref("hits_per_page"));
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
		global $lng;
		
		$this->nav_value = ($_POST[$this->getNavParameter()] != "")
			? $_POST[$this->getNavParameter()]
			: $_GET[$this->getNavParameter()];
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
			$this->getOrderDirection());
				
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
	* render table
	* @access	public
	*/
	function render()
	{
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
		if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox())))
		{
			$this->tpl->setCurrentBlock("select_all_checkbox");
			$this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $lng->txt("select_all"));
			$this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
			$this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
			$this->tpl->parseCurrentBlock();
			$footer = true;
		}
		
		// table footer numinfo
		if ($this->enabled["numinfo"] && $this->enabled["footer"])
		{
			$start = $this->offset + 1;				// compute num info
			$end = $this->offset + $this->limit;
			
			if ($end > $this->max_count or $this->limit == 0)
			{
				$end = $this->max_count;
			}
			
			if ($this->lang_support)
			{
				$numinfo = "(".$this->lng->txt("dataset")." ".$start." - ".$end." ".strtolower($this->lng->txt("of"))." ".$this->max_count.")";
			}
			else
			{
				$numinfo = "(Dataset ".$start." - ".$end." of ".$this->max_count.")";
			}
			if ($this->max_count > 0)
			{
				//$numinfo = $this->lng->txt("no_datasets");
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
			$linkbar = $this->getLinkbar();
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
		}
	}
	
	/**
	* Get previous/next linkbar.
	*
	* @author Sascha Hofmann <shofmann@databay.de>
	*
	* @return	array	linkbar or false on error
	*/
	function getLinkbar()
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
			
			if (count($offset_arr))
			{				
				$LinkBar .= "&nbsp;&nbsp;&nbsp;&nbsp;".ilUtil::formSelect($this->nav_value,
					$this->getNavParameter(), $offset_arr, false, true, 0, "ilEditSelect").
					' <input class="ilEditSubmit" type="submit" name="cmd['.$this->parent_cmd.']" value="'.
					$lng->txt("select_page").'"> ';
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
		if (count($this->multi) > 0)
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
			$this->tpl->parseCurrentBlock();
			$arrow = true;
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
	
}
?>
