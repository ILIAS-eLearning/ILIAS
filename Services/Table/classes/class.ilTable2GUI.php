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
		parent::__construct(0, false);
		$this->parent_obj = $a_parent_obj;
		$this->parent_cmd = $a_parent_cmd;
		$this->buttons = array();
		//$this->tpl = new ilTemplate("tpl.table2.html", true, true, "Services/Table");
		$this->tpl = new ilTemplate("tpl.table2.html", true, true, "Services/Table");
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

	final public function setFormAction($a_form_action)
	{
		$this->form_action = $a_form_action;
	}
	
	final public function getFormAction()
	{
		return $this->form_action;
	}

	final public function setRowTemplate($a_template, $a_template_dir = "")
	{
		$this->row_template = $a_template;
		$this->row_template_dir = $a_template_dir;
	}
	
	function addCommandButton($a_cmd, $a_text)
	{
		$this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text);
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
	
	final public function getSaveParameter()
	{
		return $this->prefix."_table_nav";
	}
	
	function setOrderLink($sort_field, $order_dir)
	{
		global $ilCtrl;
		
		$old = $_GET[$this->prefix."_table_nav"];
		
		// set order link
		$ilCtrl->setParameter($this->parent_obj,
			$this->prefix."_table_nav",
			$sort_field.":".$order_dir.":".$this->offset.":".$this->limit);
		$this->tpl->setVariable("TBL_ORDER_LINK",
			$ilCtrl->getLinkTarget($this->parent_obj, $this->parent_cmd));
		
		// set old value of nav variable
		$ilCtrl->setParameter($this->parent_obj,
			$this->prefix."_table_nav", $old);
	}

	function renderHeader()
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
		$nav = explode(":", $_GET[$this->prefix."_table_nav"]);
		
		// $nav[0] is order by
		$this->setOrderField($nav[0]);
		$this->setOrderDirection($nav[1]);
		$this->setOffset($nav[2]);
		$this->setLimit($nav[3]);
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
				$this->fillRowColor();
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
			$this->tpl->parseCurrentBlock();
		}
		
		// action row
		$action_row = false;
		
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
			
		if ($action_row)
		{
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("COLUMN_COUNTS", $this->getColumnCount());
			$this->tpl->parseCurrentBlock();
		}
		
		return $this->render();
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	}
	
	final protected function fillRowColor($a_placeholder = "CSS_ROW")
	{
		$this->css_row = ($this->css_row != "tblrow1")
			? "tblrow1"
			: "tblrow2";
		$this->tpl->setVariable($a_placeholder, $this->css_row);
	}
}
?>
