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

/** @defgroup ServicesTable Services/Table
 */

/**
* Class ilTableGUI
*
* HTML table component
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
* @ingroup ServicesTable
*/
class ilTableGUI
{
	var $title;					// table title name
	var $icon;					// table title icon
	var $icon_alt;				// table title icon alt text

	var $help_page;				// table help name
	var $help_icon;				// table help icon
	var $help_icon_alt;			// table help icon alt text

	var $header_names;			// titles of header columns
	var $header_vars;			// var names of header columns (i.e. for database order column)
	var $linkbar_vars;			// additional variables for linkbar

	var $data;					// table content
	
	var $column_count;			// no. of columns (based on element count of $this->header array)
	var $column_width;			// column width of each column (used in order until max. column from column count is reached)
								// any exceeding values are ignored
	var $max_count;				// max. count of database query
	var $limit;					// max. count of dataset per page
	var $max_limit = false;
	var $offset;				// dataset offset
	var $order_column;			// order column
	var $order_direction;		// order direction
	
	var $footer_style;			// css format for links
	var	$footer_previous;		// value of previous link
	var	$footer_next;			// value of next link
	
	var $lang_support = true;	// if a lang object is included
	var $global_tpl;			// uses global tpl (true) or a local one (false)
	var $form_name;			// the name of the parent form of the table
	var $select_all_checkbox;  // the name (or the first characters if unique) of a checkbox the should be toggled with a select all button
	var $action_buttons;  // action buttons in the table footer
	
	var $prefix;				// prefix for sort and offset fields if you have two or more tables on a page that you want to sort separately
	var $base = "";				// base script (deprecated)
	
	// default settings for enabled/disabled table modules 
	var $enabled = array(	"table"			=>  true,
							"title"			=>	true,
							"icon"			=>	true,
							"help"			=>	false,
							"content"		=>	true,
							"action"		=>	false,
							"header"        =>  true,
							"footer"		=>	true,
							"linkbar"		=>	true,
							"numinfo"		=>	true,
							"numinfo_header"		=>	false,
							"sort"			=>  true,
							"hits"          =>  false,
							"auto_sort"  	=>  true,
							"select_all" => false
						);

	// tpl styles (only one so far)
	var $styles = array(
							"table"		=> "fullwidth"
						);
	
	/**
	* Constructor
	*
	* @param	array	content data (optional)
	* @param	boolean	use global template (default)
	* @access	public
	*/
	function ilTableGUI($a_data = 0,$a_global_tpl = true)
	{
		global $ilias, $tpl, $lng;

		$this->global_tpl = $a_global_tpl;
		$this->ilias =& $ilias;
		$this->header_vars = array();
		$this->header_params = array();
		$this->enabled["form"] = true;
		$this->action_buttons = array();
		if ($this->global_tpl)
		{
			$this->tpl =& $tpl;
		}
		else
		{
			$this->tpl = new ilTemplate("tpl.table.html", true, true, "Services/Table");
		}

		$this->lng =& $lng;

		if (!$this->lng)
		{
			$this->lang_support = false;
		}

		$this->setData($a_data);
	}


	/**
	* set template
	* @access	public
	* @param	object	template object
	*/
	function setTemplate(&$a_tpl)
	{
		$this->tpl =& $a_tpl;
	}

	function &getTemplateObject()
	{
		return $this->tpl;
	}

	/**
	* set table data
	* @access	public
	* @param	array	table data
	*/
	function setData($a_data)
	{
		if (is_array($a_data))
		{
			$this->data = $a_data;
		}
	}

	function getData()
	{
		return $this->data;
	}

	/**
	* set table title
	* @access	public
	* @param	string	table title
	* @param	string	file name of title icon
	* @param	string	alternative text for title icon
	*/
	function setTitle($a_title,$a_icon = 0,$a_icon_alt = 0)
	{
		$this->title = $a_title;
		$this->icon = $a_icon;
		$this->icon_alt = $a_icon_alt;

		if (!$this->icon)
		{
			$this->enabled["icon"] = false;

			return;
		}

		if (!$this->icon_alt)
		{
			$this->icon_alt = $this->icon;
		}
		$this->enabled["icon"] = true;
	}
	
	/**
	* set table help page
	* @access	public
	* @param	string	help page file name
	* @param	string	file name of help icon
	* @param	string	alternative text for help icon
	*/
	function setHelp($a_help_page,$a_help_icon,$a_help_icon_alt = 0)
	{
		$this->help_page = $a_help_page;
		$this->help_icon = $a_help_icon;
		$this->help_icon_alt = $a_help_icon_alt;

		if (!$this->help_icon_alt)
		{
			$this->help_icon_alt = $this->help_icon;
		}
	}
	
	/**
	* set table header names
	* @access	public
	* @param	array	table header names
	*/
	function setHeaderNames($a_header_names)
	{
		$this->header_names = $a_header_names;
		$this->column_count = count($this->header_names);
	}
	
	/**
	* Returns the column count based on the number of the header row columns
	* @access	public
	* @return	int	Number of columns
	*/
	function getColumnCount()
	{
		return $this->column_count;
	}

	/**
	* set table header vars
	* @access	public
	* @param	array	table header vars
	* @param	array	additional link params
	*/
	function setHeaderVars($a_header_vars,$a_header_params = 0)
	{
		$this->header_vars = $a_header_vars;
		
		if ($a_header_params == 0 or !is_array($a_header_params))
		{
			$this->link_params = "";
		}
		else
		{
			$this->header_params = $a_header_params;	// temp. solution for linkbar

			foreach ($a_header_params as $key => $val)
			{
				$this->link_params .= $key."=".$val."&";
			}
		}
	}
	
	/**
	* set table column widths
	* @access	public
	* @param	array	column widths
	*/
	function setColumnWidth($a_column_width)
	{
		$this->column_width = $a_column_width;
	}
	
	/**
	* set one table column width
	* @access	public
	* @param	string	column width
	* @param	integer	column number
	*/
	function setOneColumnWidth($a_column_width,$a_column_number)
	{
		$this->column_width[$a_column_number] = $a_column_width;
	}

	/**
	* set max. count of database query
	* you don't need to set max count if using integrated content rendering feature
	* if max_limit is true, no limit is given -> set limit to max_count
	* @access	public
	* @param	integer	max_count
	*/
	function setMaxCount($a_max_count)
	{
		$this->max_count = $a_max_count;

		if ($this->max_limit)
		{
			$this->limit = $this->max_count;
		}
	}
	
	/**
	* set max. datasets displayed per page
	* @access	public
	* @param	integer	limit
	* @param	integer default limit
	*/
	function setLimit($a_limit = 0, $a_default_limit = 0)
	{
		$this->limit = ($a_limit) ? $a_limit : $a_default_limit;

		if ($this->limit == 0)
		{
			$this->max_limit = true;
		}
	}
	
	/**
	* Get limit.
	*/
	function getLimit()
	{
		return $this->limit;
	}


	/**
	* set prefix for sort and offset fields (if you have two or more tables on a page that you want to sort separately)
	* @access	public
	* @param	string	prefix
	*/
	function setPrefix($a_prefix)
	{
		$this->prefix = ($a_prefix) ? $a_prefix : "";
	}
	
	/**
	* set dataset offset
	* @access	public
	* @param	integer	offset
	*/
	function setOffset($a_offset)
	{
		$this->offset = ($a_offset) ? $a_offset : 0;
	}
	
	/**
	* Get offset.
	*/
	function getOffset()
	{
		return $this->offset;
	}
	
	/**
	* set order column
	* @access	public
	* @param	string	order column
	* @param	string	default column
	*/
	function setOrderColumn($a_order_column = 0,$a_default_column = 0)
	{
		// set default sort column to first column
		if (empty($a_order_column))
		{
			if (!empty($a_default_column))
			{
				$this->order_column = array_search($a_default_column,$this->header_vars);	
			}
			else
			{
				$this->order_column = 0;
				return;
			}
		}
		else
		{
			$this->order_column = array_search($a_order_column,$this->header_vars);
		}

		if ($this->order_column === false)
		{
			// if not found, set default sort column to first column
			$this->order_column = 0;
		}
	}
	
	/**
	* Get order column.
	*/
	function getOrderColumn()
	{
		return $this->order_column;
	}

	/**
	* set order direction
	* @access	public
	* @param	string	order direction
	*/
	function setOrderDirection($a_order_direction)
	{
		if ($a_order_direction == "desc")
		{
			$this->order_direction = "desc";
			$this->sort_order = "asc";
		}
		else
		{
			$this->order_direction = "asc"; // set default sort order to "ASC"
			$this->sort_order = "desc";
		}
	}

	/**
	* Get order direction
	*/
	function getOrderDirection()
	{
		return $this->order_direction;
	}

	/**
	* set order direction
	* @access	public
	* @param	string	css format for links
	* @param	string	value of previous link
	* @param	string	value of next link
	*/
	function setFooter($a_style,$a_previous = 0,$a_next = 0)
	{
		$this->footer_style = $a_style;

		$this->footer_previous = ($a_previous) ? $a_previous : "<<<";
		$this->footer_next = ($a_next) ? $a_next : ">>>";
	}

	/**
	* enables particular modules of table
	* 
	* DEPRECATED for Table2GUI: Use setEnable...<Section>() of Table2GUI instead
	* 
	* @access	public
	* @param	string	module name
	*/
	function enable($a_module_name)
	{
		if (!in_array($a_module_name,array_keys($this->enabled)))
		{
			return false;
		}

		$this->enabled[$a_module_name] = true;
	} 

	/**
	* diesables particular modules of table
	* 
	* DEPRECATED for Table2GUI: Use setEnable<Section>() of Table2GUI instead
	* 
	* @access	public
	* @param	string	module name
	*/
	function disable($a_module_name)
	{
		if (!in_array($a_module_name,array_keys($this->enabled)))
		{
			return false;
		}

		$this->enabled[$a_module_name] = false;
	}
	

	function sortData()
	{
		if($this->enabled["sort"])
		{
			$this->data = ilUtil::sortArray($this->data,$this->order_column,$this->order_direction);
		}
		$this->data = array_slice($this->data,$this->offset,$this->limit);
	}

	/**
	* render table
	* @access	public
	*/
	function render()
	{
		if($this->enabled['table'])
		{
			$this->tpl->setVariable("CSS_TABLE",$this->getStyle("table"));
		}

		// table title icon
		if ($this->enabled["icon"] && $this->enabled["title"])
		{
			$this->tpl->setCurrentBlock("tbl_header_title_icon");
			$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath($this->icon));
			$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->icon_alt);
			$this->tpl->parseCurrentBlock();
		}
		// table title help
		if ($this->enabled["help"] && $this->enabled["title"])
		{
			$this->tpl->setCurrentBlock("tbl_header_title_help");
			$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath($this->help_icon));
			$this->tpl->setVariable("TBL_HELP_LINK",$this->help_page);
			$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->help_icon_alt);
			$this->tpl->parseCurrentBlock();
		}

		// hits per page selector
		if ($this->enabled["hits"] && $this->enabled["title"])
		{
			$this->tpl->setCurrentBlock("tbl_header_hits_page");
			$this->tpl->setVariable("LIMIT",$_SESSION["tbl_limit"]);
			$this->tpl->setVariable("HITS_PER_PAGE",$this->lng->txt("hits_per_page"));
			$this->tpl->parseCurrentBlock();
		}
		
		// table title
		if ($this->enabled["title"])
		{
			$this->tpl->setCurrentBlock("tbl_header_title");
			$this->tpl->setVariable("COLUMN_COUNT",$this->column_count);
			$this->tpl->setVariable("TBL_TITLE",$this->title);
			$this->tpl->parseCurrentBlock();
		}

		// table header
		if ($this->enabled["header"])
		{
			$this->renderHeader();
		}

		// table data
		// the table content may be skipped to use an individual template blockfile
		// To do so don't set $this->data and parse your table content by yourself
		// The template block name for the blockfile MUST be 'TBL_CONTENT'

		if ($this->enabled["content"] && is_array($this->data))
		{
			if($this->enabled['auto_sort'])
			{
				$this->setMaxCount(count($this->data));
				$this->sortData();
			}
			$count = 0;
			
			foreach ($this->data as $tbl_content_row)
			{
				foreach ($tbl_content_row as $key => $tbl_content_cell)
				{
					if (is_array($tbl_content_cell))
					{
						$this->tpl->setCurrentBlock("tbl_cell_subtitle");
						$this->tpl->setVariable("TBL_CELL_SUBTITLE",$tbl_content_cell[1]);
						$this->tpl->parseCurrentBlock();
						$tbl_content_cell = "<b>".$tbl_content_cell[0]."</b>";
					}

					$this->tpl->setCurrentBlock("tbl_content_cell");
					$this->tpl->setVariable("TBL_CONTENT_CELL",$tbl_content_cell);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("tbl_content_row");
				$rowcolor = ilUtil::switchColor($count,"tblrow1","tblrow2");
				$this->tpl->setVariable("ROWCOLOR", $rowcolor);
				$this->tpl->parseCurrentBlock();
			
				$count++;
			}
		}
		// select all checkbox
		if ($this->enabled["select_all"])
		{
			if ((strlen($this->getFormName())) && (strlen($this->getSelectAllCheckbox())))
			{
				$this->tpl->setVariable('SELECT_PREFIX',$this->prefix);
				$this->tpl->setVariable("SELECT_ALL_TXT_SELECT_ALL", $this->lng->txt("select_all"));
				$this->tpl->setVariable("SELECT_ALL_CHECKBOX_NAME", $this->getSelectAllCheckbox());
				$this->tpl->setVariable("SELECT_ALL_FORM_NAME", $this->getFormName());
				if (!($this->enabled["numinfo"] && $this->enabled["footer"]))
				{
					$this->tpl->setVariable("COLUMN_COUNT", $this->getColumnCount());
				}
			}
		}
		
		// table header numinfo
		if ($this->enabled["numinfo_header"])
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
				$this->tpl->setCurrentBlock("tbl_header_numinfo");
				$this->tpl->setVariable("NUMINFO_HEADER", $numinfo);
				$this->tpl->setVariable("COLUMN_COUNT_HEADER", $this->getColumnCount());
				$this->tpl->parseCurrentBlock();
			}
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
		}
		// table footer linkbar
		if ($this->enabled["linkbar"] && $this->enabled["footer"] && $this->limit  != 0
			 && $this->max_count > 0)
		{
			$params = array(
							$this->prefix."sort_by"		=> $this->header_vars[$this->order_column],
							$this->prefix."sort_order"	=> $this->order_direction
							);
			$params = array_merge($this->header_params,$params);
			
			$layout = array(
							"link"	=> $this->footer_style,
							"prev"	=> $this->footer_previous,
							"next"	=> $this->footer_next,
							);
							
			$base = ($this->getBase() == "")
				? basename($_SERVER["PHP_SELF"])
				: $this->getBase();

			$linkbar = ilUtil::Linkbar($base,$this->max_count,$this->limit,$this->offset,$params,$layout, $this->prefix);
			$this->tpl->setCurrentBlock("tbl_footer_linkbar");
			$this->tpl->setVariable("LINKBAR", $linkbar);
			$this->tpl->parseCurrentBlock();
		}
						
		// table footer
		if ($this->enabled["footer"] && $this->max_count > 0)
		{
			$this->tpl->setCurrentBlock("tbl_footer");
			$this->tpl->setVariable("COLUMN_COUNT",$this->column_count);
			$this->tpl->parseCurrentBlock();
		}

		// action buttons
		if ($this->enabled["action"])
		{
			foreach ($this->action_buttons as $button)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", $button["name"]);
				$this->tpl->setVariable("BTN_VALUE", $button["value"]);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("ALT_ARROW", $this->lng->txt("arrow_downright.gif"));
			$this->tpl->setVariable("COLUMN_COUNTS", $this->getColumnCount());
			$this->tpl->parseCurrentBlock();
		}

		if ($this->enabled["form"])
		{
			$this->tpl->touchBlock("tbl_form_footer");
		}
		
		if($this->enabled['table'])
		{
			$this->tpl->touchBlock("tbl_table_end");
		}

		if (!$this->global_tpl)
		{
			return $this->tpl->get();
		}
	}
	
	function renderHeader()
	{
		foreach ($this->header_names as $key => $tbl_header_cell)
		{
			if (!$this->enabled["sort"])
			{
				$this->tpl->setCurrentBlock("tbl_header_no_link");
				if ($this->column_width[$key])
				{
					$this->tpl->setVariable("TBL_COLUMN_WIDTH_NO_LINK"," width=\"".$this->column_width[$key]."\"");
				}
				$this->tpl->setVariable("TBL_HEADER_CELL_NO_LINK",$tbl_header_cell);
				$this->tpl->parseCurrentBlock();
				continue;
			}
			if (($key == $this->order_column) && ($this->order_direction != ""))
			{
				if (strcmp($this->header_vars[$key], "") != 0)
				{
					$this->tpl->setCurrentBlock("tbl_order_image");
					$this->tpl->setVariable("IMG_ORDER_DIR",ilUtil::getImagePath($this->order_direction."_order.gif"));
					$this->tpl->parseCurrentBlock();
				}
			}

			$this->tpl->setCurrentBlock("tbl_header_cell");
			$this->tpl->setVariable("TBL_HEADER_CELL",$tbl_header_cell);
			
			// only set width if a value is given for that column
			if ($this->column_width[$key])
			{
				$this->tpl->setVariable("TBL_COLUMN_WIDTH"," width=\"".$this->column_width[$key]."\"");
			}

			$lng_sort_column = ($this->lang_support) ? $this->lng->txt("sort_by_this_column") : "Sort by this column";
			$this->tpl->setVariable("TBL_ORDER_ALT",$lng_sort_column);
		
			$order_dir = "asc";

			if ($key == $this->order_column)
			{ 
				$order_dir = $this->sort_order;

				$lng_change_sort = ($this->lang_support) ? $this->lng->txt("change_sort_direction") : "Change sort direction";
				$this->tpl->setVariable("TBL_ORDER_ALT",$lng_change_sort);
			}
		
			$this->setOrderLink($key, $order_dir);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("tbl_header");
		$this->tpl->parseCurrentBlock();
	}
	
	function setOrderLink($key, $order_dir)
	{
		$this->tpl->setVariable("TBL_ORDER_LINK",basename($_SERVER["PHP_SELF"])."?".$this->link_params.$this->prefix."sort_by=".$this->header_vars[$key]."&".$this->prefix."sort_order=".$order_dir."&".$this->prefix."offset=".$this->offset);
	}
	
	/*
	* set a tpl stylesheet
	* @access	public
	* @param	string	table element
	* @param	string	CSS definition
	*/
	function setStyle($a_element,$a_style)
	{
		$this->styles[$a_element] = $a_style;
	}

	/*
	* get a tpl stylesheet
	* @access	public
	* @param	string	table element
	*/
	function getStyle($a_element)
	{
		return $this->styles[$a_element];
	}

	/**
	* Set Base script name (deprecated, only use this for workarounds).
	*
	* @param	string	$a_base	Base script name (deprecated, only use this for workarounds)
	*/
	function setBase($a_base)
	{
		$this->base = $a_base;
	}

	/**
	* Get Base script name (deprecated, only use this for workarounds).
	*
	* @return	string	Base script name (deprecated, only use this for workarounds)
	*/
	function getBase()
	{
		return $this->base;
	}

	/*
	* get the name of the parent form
	* @access	public
	* @return	string	name of the parent form
	*/
	function getFormName()
	{
		return $this->form_name;
	}
	
	/*
	* set the name of the parent form
	* @access	public
	* @param	string	$a_name name of the parent form
	*/
	function setFormName($a_name = "cmd")
	{
		$this->form_name = $a_name;
	}

	/*
	* get the name of the checkbox that should be toggled with a select all button
	* @access	public
	* @return	string	name of the checkbox
	*/
	function getSelectAllCheckbox()
	{
		return $this->select_all_checkbox;
	}
	
	/*
	* set the name of the checkbox that should be toggled with a select all button
	* @access	public
	* @param	string	$a_select_all_checkbox name of the checkbox
	*/
	function setSelectAllCheckbox($a_select_all_checkbox)
	{
		$this->select_all_checkbox = $a_select_all_checkbox;
	}
	
	/*
	* Removes all action buttons from the table
	*
	* @access	public
	*/
	function clearActionButtons()
	{
		$this->action_buttons = array();
	}
	
	/*
	* Adds an action button to the table
	*
	* @param string $btn_name Name of the action button
	* @param string $btn_value Value of the action button
	* @access	public
	*/
	function addActionButton($btn_name, $btn_value)
	{
		array_push($this->action_buttons, 
			array(
				"name" => $btn_name, 
				"value" => $btn_value
			)
		);
	}
}
?>
